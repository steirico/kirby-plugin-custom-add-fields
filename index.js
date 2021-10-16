const config = window.panel;

const PAGE_CREATE_DIALOG = {
  extends: 'k-form-dialog',
  template: `
    <k-dialog
      ref="dialog"
      v-bind="$props"
      @cancel="$emit('cancel')"
      @close="$emit('close')"
      @ready="$emit('ready')"
      @submit="$refs.form.submit()"
    >
      <template v-if="text">
        <!-- eslint-disable-next-line vue/no-v-html -->
        <k-text v-html="text" />
      </template>
      <k-form
        v-if="hasFields"
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
    templateData: {
      type: Object,
      default: {}
    }
  },

  methods: {
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
      console.log("isValid");
      return true;
      // TODO: Trigger validation
      /*var
        form = this.$refs.form,
        errors = {},
        invalid = false;

      if(form) {
        form.novalidate = false;
        errors = form.$refs.fields.errors;
        invalid = true;

        Object.keys(errors).some(field => {
          var error = errors[field];
          invalid = error.$pending || error.$invalid || error.$error;
          return invalid;
        });
        return !invalid;
      } else {
        return !invalid;
      }*/
    },

    submit() {
      console.log("submit");
      if (this.isValid()){
        this.$parent.onSubmit(this.value);
      } else {
        this.$refs.dialog.error("Form is not valid");
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
