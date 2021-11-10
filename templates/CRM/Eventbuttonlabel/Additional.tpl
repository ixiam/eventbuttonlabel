{literal}
  <script type="text/javascript">
    additional_buttonLabel = '{/literal}{$additional_buttonLabel}{literal}';
    regular_buttonLabel = '{/literal}{$regular_buttonLabel}{literal}';
    CRM.$('#additional_participants').change(function() {
      additional_participants();
    });
    additional_participants();
    function additional_participants() {
      if (CRM.$("#additional_participants option:selected").val()) {
        var text = additional_buttonLabel;
      } else {
        var text = regular_buttonLabel;
      }
      if (text) {
        var buttonDetails = CRM.$('#_qf_Register_upload-bottom');
        var buttonDetailsChild = buttonDetails.children();
        CRM.$(buttonDetails).text(' ' + text).prepend(buttonDetailsChild);
      }
    }
  </script>
{/literal}
