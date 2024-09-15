@extends('admin.layouts.master')

@section('content')



    <section class="section">
      <div class="section-header">
        <h1>Configuracion</h1>
        <div class="section-header-breadcrumb">

        </div>
      </div>


      <div class="section-body">


        <div class="row">
          <div class="col-12">
            <div class="card">
                <div class="card-header">
                  <h4>JavaScript Behavior</h4>
                </div>
                <div class="card-body">
                  <div class="row">
                    <div class="col-2">
                      <div class="list-group" id="list-tab" role="tablist">
                        <a class="list-group-item list-group-item-action active" id="list-home-list" data-toggle="list" href="#list-home" role="tab">Paypal</a>
                        <a class="list-group-item list-group-item-action" id="list-stripe-list" data-toggle="list" href="#list-stripe" role="tab">Stripe</a>
                        <a class="list-group-item list-group-item-action" id="list-transfer-list" data-toggle="list" href="#list-transfer" role="tab">Transferencia</a>
                        {{-- <a class="list-group-item list-group-item-action" id="list-messages-list" data-toggle="list" href="#list-messages" role="tab">Messages</a>
                        <a class="list-group-item list-group-item-action" id="list-settings-list" data-toggle="list" href="#list-settings" role="tab">Settings</a> --}}
                      </div>
                    </div>
                    <div class="col-10">
                      <div class="tab-content" id="nav-tabContent">

                        @include('admin.payment-settings.sections.paypal-setting')

                        @include('admin.payment-settings.sections.stripe-setting')

                        @include('admin.payment-settings.sections.transfer')


                      </div>
                    </div>
                  </div>
                </div>
              </div>
          </div>

        </div>

      </div>
    </section>




@endsection


