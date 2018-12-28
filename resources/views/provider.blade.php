<!DOCTYPE HTML>
<html>
    <head>
        <meta charset="utf-8">
        <title>Validation - Counter</title>
        @extends('layout.master')

        @section("content")
 
        <!--========================login form start here======================================-->

    
        @if (Session::has('error'))
        <div class="alert alert-success" style="color: red">{{
		Session::get('error') }}</div>
        @endif
        @if (Session::has('colupdatemsg'))
        <div class="alert alert-success" style="color:green">
            {{ Session::get('colupdatemsg') }}
        </div>
        @endif
        <div class="container">
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-lg-12 col-md-12">
                            <div class="widget stacked widget-table action-table">
                                    <!-- Div for border -->
                                    <div class="col-md-12">
                                    <div class="col-md-12"><h3><a href="{{url('consortium')}}" class="btn btn-info" role="button">Back </a> &nbsp;&nbsp;<?php echo isset($SingleProvder['id'])?'Update':'Enter'; ?> Provider Details For : {{$information->configuration_name}}  ||  {{$information->remarks}}</h3><hr class="colorgraph"></div>
                                    <form name="save_provider" method="post" class="file-uploader1" action="/save_provider" enctype="multipart/form-data">
                                      
                                        <input type="hidden" name="_token" value="<?php echo csrf_token() ?>">
                                        <input type="hidden" name="configuration_id" value="{{$id}}">
                                        <?php
                                        if(isset($SingleProvder['id'])){
                                            ?>
                                            <input type="hidden" name="provider_id" value="<?php echo $SingleProvder['id'];?>">
                                            <?php
                                        }
                                        ?>
                                        <hr class="colorgraph">

                                        <!-- Div section for Confoguration information -->


                                        <!-- Div section for Provider information -->
                                        <div class="form-group col-md-12 noPaddingXS noLeftPadd" >
                                            <div class="form-group col-md-4 noPaddingXS noLeftPadd">
                                                <input value="{{ old('provider_name') }}<?php echo isset($SingleProvder['provider_name'])?$SingleProvder['provider_name']:''; ?>" type="text"
                                                       name="provider_name" id="ProviderName"
                                                       class="form-control input-lg" placeholder="Provider Name*"> <span
                                                       style="color: #ff0000">{{ $errors->add_provider->first('provider_name') }}</span>
                                            </div>

                                            <div class="form-group col-md-8 noPaddingXS noLeftPadd">
                                                <input value="{{ old('provider_url') }}<?php echo isset($SingleProvder['provider_url'])?$SingleProvder['provider_url']:''; ?>" type="text"
                                                       name="provider_url" id="ProviderUrl"
                                                       class="form-control input-lg" placeholder="Provider Url*"> <span
                                                       style="color: #ff0000">{{ $errors->add_provider->first('provider_url') }}</span>
                                            </div>

                                            <div class="form-group col-md-4 noPaddingXS noLeftPadd">
                                                <input value="{{ old('apikey') }}<?php echo isset($SingleProvder['apikey'])?$SingleProvder['apikey']:''; ?>" type="text" name="apikey"
                                                       id="name" class="form-control input-lg"
                                                       placeholder="API Key*">  <span style="color: #ff0000">{{ $errors->add_provider->first('apikey') }}</span>
                                            </div>

                                            <div class="form-group col-md-4 noPaddingXS noLeftPadd">
                                                <input value="{{ old('customer_id') }}<?php echo isset($SingleProvder['customer_id'])?$SingleProvder['customer_id']:''; ?>" type="text" name="customer_id"
                                                       id="name" class="form-control input-lg"
                                                       placeholder="Customer ID*">  <span style="color: #ff0000">{{ $errors->add_provider->first('customer_id') }}</span>
                                            </div>


                                            <div class="form-group col-md-4 noPaddingXS noLeftPadd">
                                                <input value="{{ old('requestor_id') }}<?php echo isset($SingleProvder['requestor_id'])?$SingleProvder['requestor_id']:''; ?>" type="text" name="requestor_id"
                                                       id="name" class="form-control input-lg"
                                                       placeholder="Requestor ID*"> <span style="color: #ff0000">{{ $errors->add_provider->first('requestor_id') }} </span>
                                            </div>

                                            <div class="form-group col-md-12 noPaddingXS noLeftPadd">
                                                <input value="{{ old('remarks') }}<?php echo isset($SingleProvder['remarks'])?$SingleProvder['remarks']:''; ?>" type="text" name="remarks"
                                                       id="name" class="form-control input-lg"
                                                       placeholder="Remarks"> {{ $errors->first('remarks') }}<span style="color: #ff0000"></span>
                                            </div>


                                            <div class="col-xs-12 col-md-4">
                                                <input type="submit"  value="<?php echo isset($SingleProvder['id'])?'Update Provider':'Add Provider'; ?>" 
                                                       class="btn btn-primary btn-block btn-lg" tabindex="7">
                                            </div>
                                            
                                        </div>
                                    </form>
                                    </div>
                                    <div class="col-md-12">

                                    @if (count($alllistofprovider) > 0)
                                    <div class="col-md-12">
                                    <h3>Providers List</h3>
                                    <hr class="colorgraph">
                                    <div class="widget-content">
                                    <div style="overflow-x:auto;">
                                    <table id="Providers_list" class="table table-striped table-bordered">
                                    <thead>
                                    <tr>
                                    <th>Provider Name</th>
                                    <th>Provider URL</th>
                                    <th>API Key</th>
                                    <th>Requestor Id</th>
                                    <th>Customer Id</th>
                                    <th>Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        
                                        @foreach ($alllistofprovider as $provider)
                                        <tr>
                                        <td>{{ $provider->provider_name }}</td>
                                        <td>{{ $provider->provider_url }}</td>
                                        <td>{{ $provider->apikey}}</td>
                                        <td>{{ $provider->requestor_id }}</td>
                                        <td>{{ $provider->customer_id}}</td>
                                        <td>
                                            <a href="/edit_provider/{{$information->id}}/{{$provider->id}}"><i class="fa fa-edit" aria-hidden="true"></i></a>&nbsp;&nbsp;
                                            <a onclick="return confirm('Are you sure for delete this provider?')" href="/delete_provider/{{$provider->id}}/{{$information->id}}"><i
                                                            class="fa fa-trash" aria-hidden="true"></i></a></td>
                                        </tr>
                                        @endforeach
                                        
                                    </tbody>
                                    </table>
                                    </div>

                                    </div>
                                    </div>


                                    
                                    
                                        
                                    @else
                                        Provider does not exist!
                                    @endif 
                                    </div>



                            </div>

                            <!-- /widget-header -->

                            <!-- /widget-content -->
                       
                        <!-- /widget -->
                   



                    <div class="clearfix"></div>
            </div>

        </div>

    </div>



@endsection
<!--========================login form END here======================================-->



@section("additionaljs")


<!-- Latest compiled and minified JavaScript -->
<script src="{{URL::asset('assets/js/jquery.min.js')}}"></script>
<script src="{{URL::asset('assets/js/bootstrap.min.js')}}"></script>

<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.10.11/js/jquery.dataTables.min.js">
	</script>
	<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.10.11/js/dataTables.bootstrap.min.js">
	</script>
	<script type="text/javascript" class="init">
	

$(document).ready(function() {
	
	$('#Providers_list').DataTable( {
		 "searching": true,
        "language": {
			
            "lengthMenu": "Show entry _MENU_ ",
            "zeroRecords": "No data available in table",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries",
            "infoEmpty": "No data available in table",
            "infoFiltered": "(filtered from _MAX_ total records)",
	       
        }
		
    } );
	 
} );
	</script>
<script>
<script>
    //jQuery plugin
    (function ($) {

        $.fn.uploader = function (options) {
            var settings = $.extend({
                MessageAreaText: "No files selected.",
                MessageAreaTextWithFiles: "File List:",
                DefaultErrorMessage: "Unable to open this file.",
                BadTypeErrorMessage: "We cannot accept this file type at this time.",
                acceptedFileTypes: ['pdf', 'jpg', 'gif', 'jpeg', 'bmp', 'tif', 'tiff', 'png', 'xps', 'doc', 'docx',
                    'fax', 'wmp', 'ico', 'txt', 'cs', 'rtf', 'xls', 'xlsx', 'json']
            }, options);

            var uploadId = 1;
            //update the messaging 
            $('.file-uploader__message-area p').text(options.MessageAreaText || settings.MessageAreaText);

            //create and add the file list and the hidden input list
            var fileList = $('<ul class="file-list"></ul>');
            var hiddenInputs = $('<div class="hidden-inputs hidden"></div>');
            $('.file-uploader__message-area').after(fileList);
            $('.file-list').after(hiddenInputs);

            //when choosing a file, add the name to the list and copy the file input into the hidden inputs
            $('.file-chooser__input').on('change', function () {
                var file = $('.file-chooser__input').val();
                var fileName = (file.match(/([^\\\/]+)$/)[0]);

                //clear any error condition
                $('.file-chooser').removeClass('error');
                $('.error-message').remove();

                //validate the file
                var check = checkFile(fileName);
                if (check === "valid") {

                    // move the 'real' one to hidden list 
                    $('.hidden-inputs').append($('.file-chooser__input'));

                    //insert a clone after the hiddens (copy the event handlers too)
                    $('.file-chooser').append($('.file-chooser__input').clone({withDataAndEvents: true}));

                    //add the name and a remove button to the file-list
                    $('.file-list').append('<li style="display: none;"><span class="file-list__name">' + fileName + '</span><button class="removal-button" data-uploadid="' + uploadId + '"></button></li>');
                    $('.file-list').find("li:last").show(800);

                    //removal button handler
                    $('.removal-button').on('click', function (e) {
                        e.preventDefault();

                        //remove the corresponding hidden input
                        $('.hidden-inputs input[data-uploadid="' + $(this).data('uploadid') + '"]').remove();

                        //remove the name from file-list that corresponds to the button clicked
                        $(this).parent().hide("puff").delay(10).queue(function () {
                            $(this).remove();
                        });

                        //if the list is now empty, change the text back 
                        if ($('.file-list li').length === 0) {
                            $('.file-uploader__message-area').text(options.MessageAreaText || settings.MessageAreaText);
                        }
                    });

                    //so the event handler works on the new "real" one
                    $('.hidden-inputs .file-chooser__input').removeClass('file-chooser__input').attr('data-uploadId', uploadId);

                    //update the message area
                    $('.file-uploader__message-area').text(options.MessageAreaTextWithFiles || settings.MessageAreaTextWithFiles);

                    uploadId++;

                } else {
                    //indicate that the file is not ok
                    $('.file-chooser').addClass("error");
                    var errorText = options.DefaultErrorMessage || settings.DefaultErrorMessage;

                    if (check === "badFileName") {
                        errorText = options.BadTypeErrorMessage || settings.BadTypeErrorMessage;
                    }

                    $('.file-chooser__input').after('<p class="error-message">' + errorText + '</p>');
                }
            });

            var checkFile = function (fileName) {
                var accepted = "invalid",
                        acceptedFileTypes = this.acceptedFileTypes || settings.acceptedFileTypes,
                        regex;

                for (var i = 0; i < acceptedFileTypes.length; i++) {
                    regex = new RegExp("\\." + acceptedFileTypes[i] + "$", "i");

                    if (regex.test(fileName)) {
                        accepted = "valid";
                        break;
                    } else {
                        accepted = "badFileName";
                    }
                }

                return accepted;
            };
        };
    }(jQuery));

/////init 

    $(document).ready(function () {
        $('.fileUploader').uploader({
            MessageAreaText: "Upload File Here"
        });

    });
    /*$('#sandbox-container .input-daterange').datepicker({
     changeMonth: true,
     changeYear: true,
     showButtonPanel: true,
     dateFormat: 'MM yy',
     onClose: function(dateText, inst) { 
     $(this).datepicker('setDate', new Date(inst.selectedYear, inst.selectedMonth, 1));
     }    
     });  */
    $(function () {
        $('.date-picker').datepicker({
            format: "yyyy-mm-dd",
            //viewMode: "months", 
            //minViewMode: "months"
        });
    });
</script>

@if(count($errors->welcome)>0)
<script>$(".ulTabs a[href='#menu1']").tab("show");</script>
@endif @if (Session::has('error'))
<script>$(".ulTabs a[href='#menu1']").tab("show");</script>
@endif @endsection

<script>
    function check()
    {
        var aa = $("#file").val();

        var ext = aa.split('.').pop();
        //alert(ext);
        if (!((ext == 'tsv') || (ext == 'csv') || (ext == 'xlsx') || (ext == 'xls') || (ext == 'json')))
        {
            $("#pop1").html("<span style='color:#ff0000'>The File should be xls,xlsx,csv,tsv or json extensions</span>");
            return false;
        }


    }
</script>

</head>
</html>

<!---=======================javascripts comes in bottom============================-->


