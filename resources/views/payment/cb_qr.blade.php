@extends('frontend.layouts.app')

@section('css')
  @parent
  <style type="text/css">
      .cb-pay-timer {
        font-size: 16px;
        margin-bottom: 20px;
        font-weight: bold;
      }
      .green-cb-pay-timer {
        color: green;
      }
      .red-cb-pay-timer {
        color: red;
      }
      .scan-cb-pay-text {
        font-size: 18px;font-weight: bold;
      }
  </style>
@endsection

@section('content')
<x-card>
    <div class="text-center">
        <h4 class="cb-pay-timer"></h4>
        {!! QrCode::size(200)->generate($data['merDqrCode']); !!}
        <br><br>
        <h2  class="scan-cb-pay-text">Please scan with CB Pay!</h2>
    </div>

</x-card>  


@endsection


@section('js')
    @parent
    <script type="text/javascript">
        var start = new Date;
        var timeSeconds = 300;
        var cbpayInterval;
        function CbpayTimer() {
            cbpayInterval = setInterval(function() {
                var second = (timeSeconds - Math.floor((new Date - start) / 1000));
                var cbPayTimer = $('.cb-pay-timer')
                if ( second < 30 ) {
                    cbPayTimer.addClass('red-cb-pay-timer').removeClass('green-cb-pay-timer');
                } else{
                    cbPayTimer.addClass('green-cb-pay-timer').removeClass('red-cb-pay-timer');
                }

                cbPayTimer.text(  second + " seconds");

            }, 1000);
        }

        function clearCbpayTimer()
        {
            clearInterval(cbpayInterval);
        }

      $(document).ready(function() {
            //first time call
        check();
        CbpayTimer();

        function check(){
            $.ajax({
                url: "{{ $checkTransactionUrl }}",
                success: function(result) {
                    let data = JSON.parse(result);
                    
                    if(data.transStatus == 'S'){
                        // console.log("result::", data)
                        window.location.href = data.redirect
                    }

                    //call agian till transStatus change
                    if(data.transStatus == 'P'){
                        setTimeout(function() {

                            check();

                        }, 2000);
                    }

                    if(data.transStatus == 'E'){
                        alert("Your transaction is expired.Please repay agian");
                        clearCbpayTimer();
                        window.location.href = "{{ url()->previous() }}";
                    }

                },
                error: function(result) {
                    console.log(result)
                }
            });
        }
      });
 
    </script>
@endsection