
<!--========================login form start here======================================-->
<div class="row">
<div class="col-xs-12 col-sm-121 col-md-12 ">
            <form id="getsushireport" name="getsushireport" method="post" class="file-uploader1" action="{{ url('/getsushireport') }}" enctype="multipart/form-data">
                <input type="hidden" name="_token" value="<?php echo csrf_token() ?>">
                <input type="hidden" name="Requestorurl" value="<?php echo $Requestorurl ?? ''; ?>">
                <input type="hidden" name="APIkey" value="<?php echo $apikey ?? ''; ?>">
                <input type="hidden" name="CustomerId" value="<?php echo $CustomerId ?? ''; ?>">
                <input type="hidden" name="RequestorId" value="<?php echo $RequestorIdInner ?? ''; ?>">
                <input type="hidden" name="platform" value="<?php echo $platform ?? ''; ?>">
                <div class="row">
                    <div class="col-xs-12 col-md-6">
                            Begin Date: <input type="text" autocomplete="off" name="startmonth" id="datepicker-12" class="date-picker">
                    </div>
                    <div class="col-xs-12 col-md-6">
                            End Date: <input type="text" autocomplete="off" name="endmonth" id="datepicker-10" class="date-picker">
                    </div>
                </div>
                <div class="clearfix"></div>
                <hr>
                <div class="row">
                    <div class="col-xs-12 col-md-6">
                    Reports:&nbsp;&nbsp;&nbsp;
                    <select id="reportsmater" name="ReportName" onchange="showDiv(this.value)">
                        <option  value="">Select Report</option>
                        @foreach($allreports as $report)
                        <option  value="{{$report['report_code']}}">{{$report['report_code']}}</option>
                        @endforeach
                    </select>
                    </div>
                    <div id='secondpart' style="display: none;">
                    <div class="col-xs-12 col-md-6">
                        Metric Type:&nbsp;&nbsp;&nbsp;
                        <?php
                        $allMatricType = array(
                            'Searches_Automated',
                            'Searches_Federated',
                            'Searches_Regular',
                            'Searches_Platform',
                            'Total_Item_Investigations',
                            'Unique_Item_Investigations',
                            'Unique_Title_Investigations',
                            'Total_Item_Requests',
                            'Unique_Item_Requests',
                            'Unique_Title_Requests',
                            'No_License',
                            'Limit_Exceeded'
                                                );
                        ?>
                        <select id="Metric_Type" name="metricType[]" multiple class="multiselectoption">
                            <?php foreach($allMatricType as $matric){?>
                            <option  value="<?php echo $matric; ?>"><?php echo $matric; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    </div>
                </div>
                
                <div id='secondpart-1' style="display: none;">
                
                 <div class="row">
                    <div class="col-xs-12 col-md-6">
                    Data Type:&nbsp;&nbsp;&nbsp;
                    <?php
                        $allDataType = array(
                            'Article',
                            'Book',
                            'Book Segment',
                            'Database',
                            'Dataset',
                            'Journal',
                            'Multimedia',
                            'Newspaper or Newsletter',
                            'Other',
                            'Platform',
                            'Report',
                            'Repository Item',
                            'Thesis or Dissertation'
                                                );
                        ?>
                    <select id="Data_Type" name="data_type[]" multiple class="multiselectoption">
                            <?php foreach($allDataType as $reportval){?>
                            <option  value="<?php echo $reportval; ?>"><?php echo $reportval; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-xs-12 col-md-6">
                        Access Type:&nbsp;&nbsp;&nbsp;
                        <?php
                        $AllAccessType = array(
                                                'Controlled',
                                                'OA_Gold',
                                                'Other_Free_To_Read',
                                                );
                        ?>
                        <select id="Access_Type" name="accessType[]" multiple class="multiselectoption">
                            <?php foreach($AllAccessType as $reportval){?>
                            <option  value="<?php echo $reportval; ?>"><?php echo $reportval; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-xs-12 col-md-6">
                    Access Method:&nbsp;&nbsp;&nbsp;
                     <?php
                        $AllAcessMethod = array(
                                                'Regular',
                                                'TDM',
                                                );
                        ?>
                    <select id="Access_Type" name="accessMethod[]" multiple class="multiselectoption">
                            <?php foreach($AllAcessMethod as $reportval){?>
                            <option  value="<?php echo $reportval; ?>"><?php echo $reportval; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-xs-12 col-md-6">
                       Yop:&nbsp;&nbsp;&nbsp;
                       <input type="text" name="yopparameter" value="" /> 
                    </div>
                </div>
                
                </div>
                
                <div class="clearfix"></div>
                
                <div class="modal-footer">
                    <a	class="btn btn-primary" onclick="validateDateNew()" href="javascript:void(0)">Download Report</a>
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
        $('#datepicker-12').on('changeDate', function(){
            $(this).datepicker('hide');
        });
        $('#datepicker-10').on('changeDate', function(){
            $(this).datepicker('hide');
        });
        
        $('.multiselectoption').multiselect({
            includeSelectAllOption: true,
            enableFiltering: true,
            enableCaseInsensitiveFiltering: true,
            numberDisplayed: 1
        });
    });



    function validateDateNew() {
        var date1 = $("#datepicker-12").val();
        var date2 = $("#datepicker-10").val();
        var reports = $("#reportsmater").val();
        if (!reports) {
            alert("Please select at least one report");
            return false;
        } else if (!date1) {
            alert("Please select begin date");
            return false;
        } else if (!date2) {
            alert("Please select end date");
            return false;
        } else {
            $("#getsushireport").submit();
        }
    }
    
    function showDiv(SelectedValue){
        if(SelectedValue=='DR'||SelectedValue=='IR'||SelectedValue=='TR'||SelectedValue=='PR'){
            $('#secondpart').show();
            $('#secondpart-1').show();
            
        }else{
            $('#secondpart').hide();
            $('#secondpart-1').hide();
        }
    }
</script>

