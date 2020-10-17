import CustomAddDialog from './CustomAddDialog.vue'

panel.plugin("steirico/kirby-plugin-custom-add-fields", {
  components: {
    'k-page-create-dialog': CustomAddDialog,
  }
});