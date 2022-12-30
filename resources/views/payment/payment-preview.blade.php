@extends('layouts.app')
@section('content')
   <div>
        <table>
            <thead>
                <th>Title</th>
                <th>Amount</th>
            </thead>
            <tbody>            
                <tr>
                    <td class="font-weight-bold">Purchase Amount</td>
                    <td>10,000 MMK</td>
                </tr>
                <tr>
                    <td class="font-weight-bold">Service Fees</td>
                    <td> 600 MMK</td>
                </tr>
            </tbody>
        </table>
        <form action="{{route('choose-payment')}}" method="post">
            
            {{-- Payment Selection --}}
            <h2 class="payment-method">Choose A Payment Method</h2>
            <div class="hiddenradio">
                <label>
                    <input type="radio" name="paymentMethod" id="master" value="master" />
                    <img src="{{ asset('images/payments/01_mastercardpay.png') }}" alt="" width="100px">
                </label>
                <label>
                    <input type="radio" name="paymentMethod" id="visa" value="visa" />
                    <img src="{{ asset('images/payments/01_visapay.png') }}" alt="" width="100px">
                </label>
                <label>
                    <input type="radio" name="paymentMethod" id="jcb" value="jcb" />
                    <img src="{{ asset('images/payments/01_jcbpay.png') }}" alt="" width="90px">
                </label>
                <label>
                    <input type="radio" name="paymentMethod" id="unionpay" value="unionpay" />
                    <img src="{{ asset('images/payments/01_unionpay.png') }}" alt="" width="100px">
                </label>
                <label>
                    <input type="radio" name="paymentMethod" id="mpu" value="mpu" />
                    <img src="{{ asset('images/payments/01_mpupay.png') }}" alt="" width="150px">
                </label>
                <label>
                    <input type="radio" name="paymentMethod" id="cbpay" value="cbpay" />
                    <img src="{{ asset('images/payments/01_cbpay.png') }}" alt="" width="90px">
                </label>
            </div> 
            
            <div class="row">
              <div class="col-md-12 text-right">
                  <button type="submit" class="btn btn-primary">Pay</button>
                    <a
                        href="{{ url()->previous() }}"
                        class="btn btn-secondary"
                    >
                        Back
                    </a>
              </div>
            </div>

        </form>
    </div>
@endsection

