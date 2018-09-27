
<!--========================login form start here======================================-->
<div class="row">
<div class="col-xs-12 col-sm-121 col-md-12 ">
            <form id="runconsortium" name="saveconsortium" method="post" class="file-uploader1" action="/showprogress" enctype="multipart/form-data">
                <input type="hidden" name="_token" value="<?php echo csrf_token() ?>">
                <input type="hidden" name="configurationid" value="<?php echo $configuration_id ?? ''; ?>">
                <div class="row">
                    <div class="col-xs-12 col-md-6">
                            Begin Date: <input type="text" autocomplete="off" name="begin_date" id="datepicker-12" class="date-picker">
                    </div>
                    <div class="col-xs-12 col-md-6">
                            End Date: <input type="text" autocomplete="off" name="end_date" id="datepicker-10" class="date-picker">
                    </div>
                </div>
                <div class="clearfix"></div>
                <div class="row">
                    <div class="col-xs-12 col-md-6">
                    Please Select Reports:
                    <select id="reports" name="reports[]" multiple>
                        @foreach($allreports as $report)
                        <option selected="selected" value="{{$report['report_code']}}">{{$report['report_code']}}</option>
                        @endforeach
                    </select>
                    </div>
                </div>
                <div class="clearfix"></div>
                <div class="row">
                    <div class="col-xs-12 col-md-6">
                    Please Select providers:
                    <select id="providers" name="providers[]" multiple>
                        @foreach($allproviders as $provider)
                        <option selected="selected" value="{{$provider['id']}}">{{$provider['provider_name']}}</option>
                        @endforeach
                    </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <a	class="btn btn-primary" onclick="validateDateNew('<?php echo $configuration_id; ?>')" href="javascript:void(0)">Run Configuration</a>
                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            </div>

            </form>
            <div class="clearfix"></div>

</div>
</div>
<script>
    $(function () {
        $('.date-picker').datepicker({
            format: "mm-yyyy",
            viewMode: "months",
            minViewMode: "months"
        });

    });



    jQuery(function () {
        jQuery('#reports').multiSelect();
        jQuery('#providers').multiSelect();

    });


    function validateDateNew(id) {
        var date1 = $("#datepicker-12").val();
        var date2 = $("#datepicker-10").val();
        var reports = $("#reports").val();
        var providers = $("#providers").val();
        if (!reports) {
            alert("Please select at least one report");
            return false;
        } else if (!providers) {
            alert("Please select at least one provider");
            return false;
        } else if (!date1) {
            alert("Please select begin date");
            return false;
        } else if (!date2) {
            alert("Please select end date");
            return false;
        } else {
            $('#configurationid').val(id);
            $("#runconsortium").submit();
        }
    }
</script>