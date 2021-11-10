{literal}
<script type="text/javascript">
    /**
     * Old submit-once function. Will be removed soon.
     * @deprecated
     */
    function submitOnceLabel(obj, formId, procText) {
        var buttonDetails = cj(obj);
        var buttonDetailsChild = buttonDetails.children();
        var text = ts('Processing');
        cj(buttonDetails).text(' ' + text).prepend(buttonDetailsChild);
    }
</script>
{/literal}
