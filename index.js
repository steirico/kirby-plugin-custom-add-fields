const PAGE_CREATE_DIALOG = {
  extends: 'k-page-create-dialog',
  template: `
    <k-dialog
      ref="dialog"
      :button="$t('page.draft.create')"
      :notification="notification"
      size="medium"
      theme="positive"
      @submit="$refs.form.submit()"
    >
      <k-form
        ref="form"
        :fields="fields"
        :key="addFields"
        :novalidate="true"
        v-model="page"
        @submit="submit"
        @input="input"
      />
    </k-dialog>
  `,
  props: {
    addFields: Object
  },
  computed: {
    fields() {
      return this.getFields();
    }
  },
  methods: {
    getFields() {
      var
        fields = {},
        field = {},
        endpoint = this.$route.path,
        section = 'addFields';

      if(this.addFields) {
        fields = this.addFields;
      } else {
        fields = {
          title: {
            label: this.$t("title"),
            type: "text",
            required: true,
            icon: "title"
          },
          slug: {
            label: this.$t("slug"),
            type: "text",
            required: true,
            counter: false,
            icon: "url"
          }
        }
      }

      fields.template = {
        name: "template",
        label: "Add a new page based on this template",
        type: "select",
        disabled: this.templates.length === 1,
        required: true,
        icon: "code",
        empty: false,
        options: this.templates
      }

      Object.keys(fields).forEach(name => {
        field = fields[name];

        field.section = section;
        field.endpoints = {
          field: endpoint + "/addfields/" + this.template + "/" + name,
          section: endpoint + "/addsections/" + this.template + "/" + section,
          model: endpoint
        };
      });

      return fields;
    },

    open(parent, blueprintApi, section) {
      this.parent  = parent;
      this.section = section;

      this.$api
        .get(blueprintApi + '/add-fields', {section: section})
        .then(response => {
          if(response.skipDialog){
            this.submit(response.page);
            return;
          }
          this.templates = response.map(blueprint => {
            return {
              value: blueprint.name,
              text: blueprint.title,
              addFields: blueprint.addFields
            };
          });

          if (this.templates[0]) {
            this.page.template = this.templates[0].value;
            this.template = this.templates[0].value;
            this.addFields = this.templates[0].addFields;
          }

          this.$refs.dialog.open();
        })
        .catch(error => {
          this.$store.dispatch("notification/error", error);
        });
    },

    input() {
      if(this.page.template !== this.template){
        var
          oTemplate = {},
          template = this.page.template;

        this.template  = template;

        oTemplate = this.templates.find(function(tpl){
          return tpl.value === template;
        });
        this.addFields = oTemplate.addFields;
      }
    },

    submit(forced) {
      let data = {};
      
      if(forced){
        data = forced;
      } else {
        data = {
          template: this.page.template,
          slug: this.page.slug || Date.now(),
          content: this.page
        };
      }

      if (data.content && data.content.addFields) {
        data.content.addFields = undefined;
      }

      this.$api
        .post(this.parent + "/children", data)
        .then(page => {
          this.success({
            route: page.parent ? this.$api.pages.link(page.parent.id) : '/',
            message: ":)",
            event: "page.create"
          });
          this.$router.go();
        })
        .catch(error => {
          this.$refs.dialog.error(error.message);
        });
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
