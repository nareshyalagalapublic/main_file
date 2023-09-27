
@extends('layouts.main')
@section('page-title')
    {{ __('Vendor-Detail') }}
@endsection
@section('page-breadcrumb')
    {{ __('Vendor-Detail') }}
@endsection
@push('scripts')
    <script>
        $(document).on('click', '#billing_data', function() {
            $("[name='shipping_name']").val($("[name='billing_name']").val());
            $("[name='shipping_country']").val($("[name='billing_country']").val());
            $("[name='shipping_state']").val($("[name='billing_state']").val());
            $("[name='shipping_city']").val($("[name='billing_city']").val());
            $("[name='shipping_phone']").val($("[name='billing_phone']").val());
            $("[name='shipping_zip']").val($("[name='billing_zip']").val());
            $("[name='shipping_address']").val($("[name='billing_address']").val());
        })
    </script>
    <script>
        var scrollSpy = new bootstrap.ScrollSpy(document.body, {
            target: '#useradd-sidenav',
            offset: 300
        })
    </script>
@endpush
@push('css')
<style>
    .cus-card {
        min-height: 204px;
    }
</style>
@endpush
@section('page-action')
<div>
    @can('bill create')
        <a href="{{ route('bill.create',$vendor->id) }}" class="btn btn-sm btn-primary">
            <i class="ti ti-plus">  </i>{{__('Create Bill')}}
        </a>
    @endcan
        <a href="{{ route('vendor.statement',$vendor['id']) }}" class="btn btn-sm btn-primary">
            {{__('Statement')}}
        </a>
        @if($vendor->user->is_disable == 1)
            @can('vendor edit')
                <a  class="btn btn-sm btn-primary action-btn px-1"
                    data-url="{{ route('vendors.edit',$vendor['user_id']) }}" data-ajax-popup="true"  data-size="lg"
                    data-bs-toggle="tooltip" title=""
                    data-title="{{ __('Edit Vendor') }}"
                    data-bs-original-title="{{ __('Edit') }}">
                    <i class="ti ti-pencil text-white"></i>
                </a>
            @endcan
        @endif
</div>
@endsection
@section('content')
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-4">
                </div>
                <div class="col-md-8 mt-4">
                    <ul class="nav nav-pills nav-fill cust-nav information-tab" id="pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="vendor-details-tab" data-bs-toggle="pill"
                                data-bs-target="#vendor-details" type="button">{{ __('Details') }}</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="vendor-bills-tab" data-bs-toggle="pill"
                                data-bs-target="#vendor-bills" type="button">{{ __('Bill') }}</button>
                        </li>
                        @stack('vendor_purchase_tab')
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="vendor-payment-tab" data-bs-toggle="pill"
                                data-bs-target="#vendor-payment" type="button">{{ __('Payment') }}</button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-lg-12">
            <div class="tab-content" id="pills-tabContent">

                <div class="tab-pane fade active show" id="vendor-details" role="tabpanel"
                        aria-labelledby="pills-user-tab-1">
                    <div class="row">
                        <div class="col-md-4 col-lg-4 col-xl-4">
                            <div class="card pb-0 customer-detail-box">
                                <div class="card-body cus-card">
                                    <h5 class="card-title">{{__('Vendor Info')}}</h5>
                                    <p class="card-text mb-0">{{$vendor->name}}</p>
                                    <p class="card-text mb-0">{{$vendor->email}}</p>
                                    <p class="card-text mb-0">{{$vendor->contact}}</p>
                                    @if(!empty($customFields) && count($vendor->customField)>0)
                                        @foreach($customFields as $field)
                                        <p class="card-text mb-0">
                                            <strong >{{$field->name}} : </strong>{{!empty($vendor->customField[$field->id])?$vendor->customField[$field->id]:'-'}}
                                        </p>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-4 col-xl-4">
                            <div class="card pb-0 customer-detail-box">
                                <div class="card-body cus-card">
                                    <h3 class="card-title">{{__('Billing Info')}}</h3>
                                    <p class="card-text mb-0">{{$vendor->billing_name}}</p>
                                    <p class="card-text mb-0">{{$vendor->billing_address}}</p>
                                    <p class="card-text mb-0">{{$vendor->billing_city.' ,'. $vendor->billing_state .' ,'.$vendor->billing_zip}}</p>
                                    <p class="card-text mb-0">{{$vendor->billing_country}}</p>
                                    <p class="card-text mb-0">{{$vendor->billing_phone}}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-4 col-xl-4">
                            <div class="card pb-0 customer-detail-box">
                                <div class="card-body cus-card">
                                    <h3 class="card-title">{{__('Shipping Info')}}</h3>
                                    @if(company_setting('bill_shipping_display')=='on')
                                    <p class="card-text mb-0">{{$vendor->shipping_name}}</p>
                                    <p class="card-text mb-0">{{$vendor->shipping_address}}</p>
                                    <p class="card-text mb-0">{{$vendor->shipping_city.' ,'. $vendor->shipping_state .' ,'.$vendor->shipping_zip}}</p>
                                    <p class="card-text mb-0">{{$vendor->shipping_country}}</p>
                                    <p class="card-text mb-0">{{$vendor->shipping_phone}}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card pb-0">
                                <div class="card-body">
                                    <h3 class="card-title">{{__('Company Info')}}</h3>
                                    <div class="row">
                                        @php
                                            $totalBillSum=$vendor->vendorTotalBillSum($vendor['id']);
                                            $totalBill=$vendor->vendorTotalBill($vendor['id']);
                                            $averageSale=($totalBillSum!=0)?$totalBillSum/$totalBill:0;
                                        @endphp
                                        <div class="col-md-3 col-sm-6">
                                            <div class="p-2">
                                                <p class="card-text mb-0">{{__('Vendor Id')}}</p>
                                                <h6 class="report-text mb-3">{{ Modules\Account\Entities\Vender::vendorNumberFormat($vendor->vendor_id)}}</h6>
                                                <p class="card-text mb-0">{{__('Total Sum of Bills')}}</p>
                                                <h6 class="report-text mb-0">{{ currency_format_with_sym($totalBillSum)}}</h6>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-6">
                                            <div class="p-2">
                                                <p class="card-text mb-0">{{__('Date of Creation')}}</p>
                                                <h6 class="report-text mb-3">{{ company_date_formate($vendor->created_at)}}</h6>
                                                <p class="card-text mb-0">{{__('Quantity of Bills')}}</p>
                                                <h6 class="report-text mb-0">{{$totalBill}}</h6>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-6">
                                            <div class="p-2">
                                                <p class="card-text mb-0">{{__('Balance')}}</p>
                                                <h6 class="report-text mb-3">{{ currency_format_with_sym($vendor->balance)}}</h6>
                                                <p class="card-text mb-0">{{__('Average Sales')}}</p>
                                                <h6 class="report-text mb-0">{{ currency_format_with_sym($averageSale)}}</h6>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-6">
                                            <div class="p-2">
                                                <p class="card-text mb-0">{{__('Overdue')}}</p>
                                                <h6 class="report-text mb-3">{{ currency_format_with_sym($vendor->vendorOverdue($vendor->id))}}</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="vendor-bills" role="tabpanel"
                        aria-labelledby="pills-user-tab-2">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body table-border-style">
                                    <h5 class="d-inline-block mb-5">{{__('Bills')}}</h5>
                                    <div class="table-responsive">
                                        <table class="table datatable">
                                            <thead>
                                                <tr>
                                                    <th>{{__('Bill')}}</th>
                                                    <th>{{__('Bill Date')}}</th>
                                                    <th>{{__('Due Date')}}</th>
                                                    <th>{{__('Due Amount')}}</th>
                                                    <th>{{__('Status')}}</th>
                                                    @if(Gate::check('bill edit') || Gate::check('bill delete') || Gate::check('bill show'))
                                                        <th width="10%"> {{__('Action')}}</th>
                                                    @endif
                                                </tr>
                                            </thead>
                                            <tbody>
                                            @forelse ($vendor->vendorBill($vendor->id) as $bill)
                                                <tr class="font-style">
                                                    <td class="Id">
                                                        @can('bill show')
                                                            <a href="{{ route('bill.show',\Crypt::encrypt($bill->id)) }}" class="btn btn-outline-primary">{{ Modules\Account\Entities\Bill::billNumberFormat($bill->bill_id) }}
                                                            </a>
                                                        @else
                                                            <a  class="btn btn-outline-primary">{{ Modules\Account\Entities\Bill::billNumberFormat($bill->bill_id) }}</a>
                                                        @endcan
                                                    </td>
                                                    <td>{{ company_date_formate($bill->bill_date) }}</td>
                                                    <td>
                                                        @if(($bill->due_date < date('Y-m-d')))
                                                            <p class="text-danger"> {{  company_date_formate($bill->due_date) }}</p>
                                                        @else
                                                            {{  company_date_formate($bill->due_date) }}
                                                        @endif
                                                    </td>
                                                    <td>{{ currency_format_with_sym($bill->getDue())  }}</td>
                                                    <td>
                                                        @if($bill->status == 0)
                                                            <span class="badge bg-primary p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$bill->status]) }}</span>
                                                        @elseif($bill->status == 1)
                                                            <span class="badge bg-warning p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$bill->status]) }}</span>
                                                        @elseif($bill->status == 2)
                                                            <span class="badge bg-danger p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$bill->status]) }}</span>
                                                        @elseif($bill->status == 3)
                                                            <span class="badge bg-info p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$bill->status]) }}</span>
                                                        @elseif($bill->status == 4)
                                                            <span class="badge bg-success p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$bill->status]) }}</span>
                                                        @endif
                                                    </td>
                                                    @if(Gate::check('bill edit') || Gate::check('bill delete') || Gate::check('bill show'))
                                                        <td class="Action">
                                                            <span>
                                                            @can(' bill duplicate')
                                                                    <div class="action-btn bg-secondary ms-2">

                                                                        {!! Form::open(['method' => 'get', 'route' => ['bill.duplicate', $bill->id],'id'=>'bill-duplicate-form-'.$bill->id]) !!}
                                                                            <a  class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip"  title="{{ __('Duplicate Bill') }}" data-original-title="{{__('Duplicate')}}" data-confirm="{{__('You want to confirm this action. Press Yes to continue or Cancel to go back')}}" data-confirm-yes="document.getElementById('bill-duplicate-form-{{$bill->id}}').submit();">
                                                                                <i class="ti ti-copy text-white text-white"></i>
                                                                            </a>
                                                                        {!! Form::close() !!}

                                                                    </div>
                                                                @endcan
                                                                @can('bill show')
                                                                    <div class="action-btn bg-warning ms-2">
                                                                        <a href="{{ route('bill.show',\Crypt::encrypt($bill->id)) }}" class="mx-3 btn btn-sm  align-items-center" data-bs-toggle="tooltip" title="{{__('Show')}}" data-original-title="{{__('Detail')}}">
                                                                            <i class="ti ti-eye text-white text-white"></i>
                                                                        </a>
                                                                    </div>
                                                                @endcan
                                                                @can('bill edit')
                                                                <div class="action-btn bg-info ms-2">
                                                                    <a href="{{ route('bill.edit',\Crypt::encrypt($bill->id)) }}" class="mx-3 btn btn-sm align-items-center" data-toggle="popover" title="Edit" data-original-title="{{__('Edit')}}">
                                                                        <i class="ti ti-pencil text-white"></i>
                                                                    </a>
                                                                </div>
                                                            @endcan
                                                            @can('bill delete')
                                                                <div class="action-btn bg-danger ms-2">
                                                                    {{Form::open(array('route'=>array('bill.destroy', $bill->id),'class' => 'm-0'))}}
                                                                        @method('DELETE')
                                                                        <a
                                                                            class="mx-3 btn btn-sm  align-items-center bs-pass-para show_confirm"
                                                                            data-toggle="popover" title="Delete" data-bs-original-title="Delete"
                                                                            aria-label="Delete" data-confirm="{{__('Are You Sure?')}}" data-text="{{__('This action can not be undone. Do you want to continue?')}}"  data-confirm-yes="delete-form-{{$bill->id}}"><i
                                                                                class="ti ti-trash text-white text-white"></i></a>
                                                                    {{Form::close()}}
                                                                </div>
                                                            @endcan
                                                            </span>
                                                        </td>
                                                    @endif
                                                </tr>
                                                @empty
                                                    @include('layouts.nodatafound')
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @stack('vendor_purchase_div')

                <div class="tab-pane fade" id="vendor-payment" role="tabpanel" aria-labelledby="pills-user-tab-3">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body table-border-style">
                                    <h5 class="d-inline-block mb-5">{{__('Payment')}}</h5>
                                    <div class="table-responsive">
                                        <table class="table mb-0">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('Date') }}</th>
                                                    <th>{{ __('Amount') }}</th>
                                                    <th>{{ __('Account') }}</th>
                                                    <th>{{ __('Category') }}</th>
                                                    <th>{{ __('Reference') }}</th>
                                                    <th>{{ __('Description') }}</th>
                                                    <th>{{ __('Payment Receipt') }}</th>
                                                    @if (Gate::check('expense payment delete') || Gate::check('expense payment edit'))
                                                        <th>{{ __('Action') }}</th>
                                                    @endif
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($vendor->vendorPayment($vendor->id) as $payment)
                                                    <tr class="font-style">
                                                        <td>{{ company_date_formate($payment->date) }}</td>
                                                        <td>{{ currency_format_with_sym($payment->amount) }}</td>
                                                        <td>{{ !empty($payment->bankAccount) ? $payment->bankAccount->bank_name . ' ' . $payment->bankAccount->holder_name : '' }}
                                                        </td>
                                                        @if (module_is_active('ProductService'))
                                                            <td>{{ !empty($payment->category) ? $payment->category->name : '-' }}</td>
                                                        @else
                                                            <td>-</td>
                                                        @endif
                                                        <td>{{ !empty($payment->reference) ? $payment->reference : '-' }}</td>
                                                        <td>
                                                            <p style="white-space: nowrap;
                                                                width: 200px;
                                                                overflow: hidden;
                                                                text-overflow: ellipsis;">{{  !empty($payment->description) ? $payment->description : '' }}
                                                            </p>
                                                        </td>
                                                        <td>
                                                            @if (!empty($payment->add_receipt))
                                                                <div class="action-btn bg-primary ms-2">
                                                                    <a href="{{ get_file($payment->add_receipt) }}" download=""
                                                                        class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip"
                                                                        title="{{ __('Download') }}" target="_blank">
                                                                        <i class="ti ti-download text-white"></i>
                                                                    </a>
                                                                </div>
                                                                <div class="action-btn bg-secondary ms-2">
                                                                    <a href="{{ get_file($payment->add_receipt) }}"
                                                                        class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip"
                                                                        title="{{ __('Show') }}" target="_blank">
                                                                        <i class="ti ti-crosshair text-white"></i>
                                                                    </a>
                                                                </div>
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                        @if (Gate::check('expense payment delete') || Gate::check('expense payment edit'))
                                                            <td class="action text-end">
                                                                @can('expense payment edit')
                                                                    <div class="action-btn bg-info ms-2">
                                                                        <a  class="mx-3 btn btn-sm align-items-center"
                                                                            data-url="{{ route('payment.edit', $payment->id) }}"
                                                                            data-ajax-popup="true" data-title="{{ __('Edit Payment') }}"
                                                                            data-size="lg" data-bs-toggle="tooltip" title="{{ __('Edit') }}"
                                                                            data-original-title="{{ __('Edit') }}">
                                                                            <i class="ti ti-pencil text-white"></i>
                                                                        </a>
                                                                    </div>
                                                                @endcan
                                                                @can('expense payment delete')
                                                                    <div class="action-btn bg-danger ms-2">
                                                                        {{ Form::open(['route' => ['payment.destroy', $payment->id], 'class' => 'm-0']) }}
                                                                        @method('DELETE')
                                                                        <a
                                                                            class="mx-3 btn btn-sm  align-items-center bs-pass-para show_confirm"
                                                                            data-bs-toggle="tooltip" title=""
                                                                            data-bs-original-title="Delete" aria-label="Delete"
                                                                            data-confirm="{{ __('Are You Sure?') }}"
                                                                            data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                                            data-confirm-yes="delete-form-{{ $payment->id }}"><i
                                                                                class="ti ti-trash text-white text-white"></i></a>
                                                                        {{ Form::close() }}
                                                                    </div>
                                                                @endcan
                                                            </td>
                                                        @endif
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
