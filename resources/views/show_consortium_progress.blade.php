<!DOCTYPE HTML>
<?php
//print_r($error_report);
?>
<html>
    <head>
        <meta charset="utf-8">
        <title>Consortium Report - Counter</title>
        @extends('layout.master')

        @section("content")

        <!--========================login form start here======================================-->
    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-lg-12 col-md-12">
                <div class="pull-left">
                    <h3>Harvesting progress report of <strong>{{$configuration_name}} </strong>and Transaction Id: <span id="transactionvalue"></span></h3></div><div class="pull-right"><h3><a href="{{url('consortium')}}" class="btn btn-primary btnBlockxs ">Go to configuration</a></h3></div> <div class="clearfix"></div>
                <div class="validationReport">

                    <div class="col-md-12">
                        <?php if ($success == '1') { ?>

                            <p>Reports Harvesting Request is in Progress, This will take time depending upon the No of providers and reports requested.</p>
                            <p>Note: - Do not refresh or click on the back button while harvesting is in process. Your download link will appear here shortly.</p>
                            <div class="panel panel-body center-block text-center">
                                <i id="downloadcomplete" class="fa fa-spinner fa-spin" style="font-size:100px;color:blue"></i>
                                <div id="downloadid" style="display: none;">
                                    <h5><a href="<?php echo "upload/json/" . $uploaded_file; ?>" download="{{$uploaded_file}}">Click here for download the report</a></h5>
                                </div>
                            </div>

                        <?php } else { ?>

                            <p>Please review the errors below:</p>



                        <?php } ?>
                    </div>







                    <div class="clearfix"></div>


                </div>


            </div>



            <div class="col-xs-12 col-sm-12 col-lg-12 col-md-12">
                <div class="pull-left">
                    <h3>Progress Status</h3></div> <div class="clearfix"></div>
                <div class="validationReport">
                    <div class="col-md-12">
                        <?php if ($success == '1') { ?>
                            <p>Progress Status</p>
                            <div class="panel panel-body">
                                <div id="showstatus">
                                </div>
                            </div>
                        <?php } else { ?>

                            <p>Please review the errors below:</p>

                        <?php } ?>
                    </div>
                    <div class="clearfix"></div>
                </div>


            </div>





        </div>
    </div>
    <!--========================login form END here======================================-->

</div>

<?php
$t = microtime(true);
$micro = sprintf("%06d", ($t - floor($t)) * 1000000);
$d = new DateTime(date('Y-m-d H:i:s.' . $micro, $t));
$TransactionId = $d->format("YmdHisu");
?>
<script type="text/javascript" class="init">
    $(document).ready(function () {

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $('#transactionvalue').html('<strong>' + '<?php echo $TransactionId; ?>' + '</strong>')
        $.get('/runconsortium/<?php echo $id; ?>/<?php echo $TransactionId; ?>/<?php echo $begin_date; ?>/<?php echo $end_date; ?>', function (response) {
            //got file name
            $('#downloadcomplete').hide();
            $('#downloadid').show();
            $('#downloadid').html('<a href="' + response + '">Click Here To Download your  File in .zip Format</a>')

        });



        setInterval(get_records, 10000);


        function get_records() {
            $.get('/showprogressrecord/<?php echo $id; ?>/<?php echo $TransactionId; ?>/<?php echo $begin_date; ?>/<?php echo $end_date; ?>', function (response) {
                $('#showstatus').html(response);
            });
        }




    });
</script>





@endsection

<!---=======================javascripts comes in bottom============================-->
<!-- Latest compiled and minified JavaScript -->
<script src="{{URL::asset('assets/js/jquery.min.js')}}"></script>
<script src="{{URL::asset('assets/js/bootstrap.min.js')}}"></script>


</body>
</html>
