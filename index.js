const isLegacy = panel.$system ? false : true;

const PAGE_CREATE_COMMON = {
  methods: {
    input() {
      var
        data = isLegacy ? this.page : this.value,
        template = data.template;

      if(template !== this.template){
        this.template  = template;
        if(isLegacy) {  
          this.addFields = this.templateData[template];
          this.$set(this.page, "template", template);
        } else {
          this.$props.fields = this.$props.templateData[template];
        }
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
            field = fields[fieldName],
            error = errors[fieldName];
            
          invalid = field.length > 0 && error && (error.$pending || error.$invalid || error.$error);
          return invalid;
        });
        return !invalid;
      } else {
        return !invalid;
      }
    }
  }
};

const PAGE_CREATE_DIALOG = {
  extends: 'k-form-dialog',
  mixin: [PAGE_CREATE_COMMON],
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

    submit() {
      if (this.isValid()){
        this.$parent.onSubmit(this.value);
      } else {
        this.$refs.dialog.error(this.$t("error.form.incomplete"));
      }
    }
  }
};

const LEGACY_PAGE_CREATE_DIALOG = {
  extends: 'k-page-create-dialog',
  mixin: [PAGE_CREATE_COMMON],
  template: `
    <k-dialog
      ref="dialog"
      :submit-button="$t('page.draft.create')"
      :notification="notification"
      size="medium"
      theme="positive"
      @submit="$refs.form.submit()"
    >
      <k-form
        ref="form"
        :fields="fields"
        :novalidate="true"
        :key="template"
        v-model="page"
        @submit="submit"
        @input="input"
      />
    </k-dialog>
  `,
  data() {
    return {
      notification: null,
      template: '',
      page: {},
      addFields: {}
    };
  },
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
    fields() {
      var
        fields = this.addFields,
        field = {};

      Object.keys(fields).forEach(name => {
        field = fields[name];

        // Ensure defaults
        if (name != "title" && name != "template" && this.page[name] === undefined){
          if (field.default !== null && field.default !== undefined) {
            this.$set(this.page, name, this.$helper.clone(field.default));
          } else {
            this.$set(this.page, name, null);
          }
        }

        if (name === "title" && this.page[name] === "") {
          if (field.default !== null && field.default !== undefined) {
            this.$set(this.page, name, this.$helper.clone(field.default));
          } else {
            this.$set(this.page, name, "");
          }
        }
      });

      return fields;
    }
  },

  methods: {
    open(parent, blueprintApi, section) {
      this.parent  = parent;
      this.section = section;

      this.$api
        .get(blueprintApi + '/addfields', {section: section})
        .then(response => {
          var
            props = response.props;

          this.templateData = props.templateData;
          this.template = props.value.template;
          this.addFields = props.fields;
          this.page = props.value;

          if(props.options && props.options.skip){
            this.submit();
          } else {
            this.$refs.dialog.open();
          }
        })
        .catch(error => {
          this.$store.dispatch("notification/error", error);
        });
    },

    submit() {
      if (this.isValid()){
        this.$api
          .post(this.parent + "/children/addfields", this.page)
          .then(response => {

            this.success({
              route: response.redirect,
              message: ":)",
              event: response.event
            });

            if(response.redirect === ("/" + this.parent)){
              this.$router.go();
            }
          })
          .catch(error => {
            this.$refs.dialog.error(error.message);
          });
      } else {
        this.$refs.dialog.error("Form is not valid");
      }
    }
  }
};

panel.plugin("steirico/kirby-plugin-custom-add-fields", {
  components: {
    'k-page-create-dialog': isLegacy ? LEGACY_PAGE_CREATE_DIALOG : PAGE_CREATE_DIALOG
  },
  use: [
    function(Vue) {
      const
        VUE_COMPONENTS = Vue.options.components;

      Vue.mixin(PAGE_CREATE_COMMON);

      Object.keys(VUE_COMPONENTS).forEach(componentName => {
        const COMPONENT = {
          components: {
            'k-page-create-dialog': isLegacy ? LEGACY_PAGE_CREATE_DIALOG : PAGE_CREATE_DIALOG
          },
          extends: VUE_COMPONENTS[componentName]
        };
        Vue.component(componentName, COMPONENT);
      });
    }
  ]
});
