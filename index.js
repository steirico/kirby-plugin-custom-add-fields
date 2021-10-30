const config = window.panel;

const PAGE_CREATE_DIALOG = {
  extends: 'k-form-dialog',
  template: `
    <k-dialog
      ref="dialog"
      v-bind="$props"
      @cancel="$emit('cancel')"
      @close="$emit('close')"
      @ready="ready"
      @submit="$refs.form.submit()"
    >
      <template v-if="skipDialog">
        <k-icon class="k-loader" type="loader" />
      </template>
      <k-form
        v-else-if="hasFields"
        ref="form"
        :value="model"
        :fields="fields"
        :novalidate="true"
        @submit="submit"
        @input="input"
      />
      <k-box v-else theme="negative">
        This form dialog has no fields
      </k-box>
    </k-dialog>
  `,
  props: {
    options: {
      type: Object,
      default: {}
    },
    templateData: {
      type: Object,
      default: {}
    }
  },

  computed: {
    skipDialog() {
      return this.$props.options && this.$props.options.skip;
    }
  },
  methods: {
    ready() {
      if(this.skipDialog) {
        this.submit();
      }
    },
    input() {
      if(this.oldTemplate !== this.value.template){
        var
          oTemplate = {},
          template = this.value.template;

        this.oldTemplate  = template;

        oTemplate = this.$props.templateData[template];
        this.$props.fields = oTemplate;
      }
    },

    isValid() {
      var
        form = this.$refs.form,
        fieldset = {},
        fields = {},
        errors = {},
        invalid = false;

      if(form) {
        form.novalidate = false;
        fieldset = form.$refs.fields
        fields = fieldset.$refs
        errors = fieldset.errors;
        invalid = true;

        Object.keys(fields).some(fieldName => {
          var
            error = errors[fieldName];

          invalid = error.$pending || error.$invalid || error.$error;
          return invalid;
        });
        return !invalid;
      } else {
        return !invalid;
      }
    },

    submit() {
      if (this.isValid()){
        this.$parent.onSubmit(this.value);
      } else {
        this.$refs.dialog.error(this.$t("error.form.incomplete"));
      }
    }
  }
};

panel.plugin("steirico/kirby-plugin-custom-add-fields", {
  components: {
    'k-page-create-dialog': PAGE_CREATE_DIALOG
  },
  use: [
    function(Vue) {
      const
        VUE_COMPONENTS = Vue.options.components;

      Object.keys(VUE_COMPONENTS).forEach(componentName => {
        const COMPONENT = {
          components: {
            'k-page-create-dialog': PAGE_CREATE_DIALOG
          },
          extends: VUE_COMPONENTS[componentName]
        };
        Vue.component(componentName, COMPONENT);
      });
    }
  ]
});
