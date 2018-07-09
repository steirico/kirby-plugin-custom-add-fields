<div class="modal-content modal-content-medium" data-slug-table="">
  <?php echo $form ?>
  <script>if (typeof customAddFieldsScript === "function") customAddFieldsScript();</script>
</div>

<script>
customAddFieldsScript = function() {
  $.slug.table = <?php echo slugTable() ?>;

  var
    modal = $('.modal-content'),
    newModal = {},
    title = modal.find('[name=title]'),
    uid   = modal.find('[name=uid]'),
    template = modal.find('[name=template]');
    icon = modal.find('.field-name-template').find('.icon');

  template.on('change', function(){
    icon.addClass('fa-spinner fa-pulse');

    $.get('<?php echo $url ?>/' + this.value, function(data){
      newModal = $.parseHTML(data.content.trim(), null, true);
      modal.html(newModal[0].innerHTML);
    });
    
  });

  title.on('keyup', function() {
    uid.val($.slug(title.val()));
  });

  uid.on('blur', function() {
    uid.val($.slug(uid.val()));
  });
};
customAddFieldsScript();
</script>
