@extends('layouts.main')
@section('page-title')
    {{__('Purchase Detail')}}
@endsection
@push('scripts')
    <script>
        $(document).on('click', '#shipping', function () {
            var url = $(this).data('url');
            var is_display = $("#shipping").is(":checked");
            $.ajax({
                url: url,
                type: 'get',
                data: {
                    'is_display': is_display,
                },
                success: function (data) {
                }
            });
        })

    </script>
@endpush
@section('page-breadcrumb')
     {{__('Purchase')}},
     {{\Modules\Pos\Entities\Purchase::purchaseNumberFormat($purchase->purchase_id) }}
@endsection

@section('content')

    @can('purchase send')
        @if($purchase->status!=4)
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row timeline-wrapper">
                                <div class="col-md-6 col-lg-4 col-xl-4">
                                    <div class="timeline-icons"><span class="timeline-dots"></span>
                                        <i class="ti ti-plus text-primary"></i>
                                    </div>
                                    <h6 class="text-primary my-3">{{__('Create Purchase')}}</h6>
                                    <p class="text-muted text-sm mb-3"><i class="ti ti-clock mr-2"></i>{{__('Created on ')}}{{company_date_formate($purchase->purchase_date)}}</p>
                                    @can('purchase edit')
                                        <a href="{{ route('purchase.edit',\Crypt::encrypt($purchase->id)) }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-original-title="{{__('Edit')}}"><i class="ti ti-pencil mr-2"></i>{{__('Edit')}}</a>

                                    @endcan
                                </div>
                                <div class="col-md-6 col-lg-4 col-xl-4">
                                    <div class="timeline-icons"><span class="timeline-dots"></span>
                                        <i class="ti ti-mail text-warning"></i>
                                    </div>
                                    <h6 class="text-warning my-3">{{__('purchase send')}}</h6>
                                    <p class="text-muted text-sm mb-3">
                                        @if($purchase->status!=0)
                                            <i class="ti ti-clock mr-2"></i>{{__('Sent on')}} {{company_date_formate($purchase->send_date)}}
                                        @else
                                            @can('purchase send')
                                                <small>{{__('Status')}} : {{__('Not Sent')}}</small>
                                            @endcan
                                        @endif
                                    </p>

                                    @if($purchase->status==0)
                                            @can('purchase send')
                                                <a href="{{ route('purchase.sent',$purchase->id) }}" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" data-original-title="{{__('Mark Sent')}}"><i class="ti ti-send mr-2"></i>{{__('Send')}}</a>
                                            @endcan
                                    @endif
                                </div>
                                <div class="col-md-6 col-lg-4 col-xl-4">
                                    <div class="timeline-icons"><span class="timeline-dots"></span>
                                        <i class="ti ti-report-money text-info"></i>
                                    </div>
                                    <h6 class="text-info my-3">{{__('Get Paid')}}</h6>
                                    <p class="text-muted text-sm mb-3">{{__('Status')}} : {{__('Awaiting payment')}} </p>
                                    @if($purchase->status!= 0)
                                                @can('purchase payment create')
                                                    <a href="#" data-url="{{ route('purchase.payment',$purchase->id) }}" data-ajax-popup="true" data-title="{{__('Add Payment')}}" class="btn btn-sm btn-info" data-original-title="{{__('Add Payment')}}"><i class="ti ti-report-money mr-2"></i>{{__('Add Payment')}}</a> <br>
                                                @endcan
                                    @endif

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endcan

    @if(\Auth::user()->type=='company')
        @if($purchase->status!=0)
            <div class="row justify-content-between align-items-center mb-3">
                <div class="col-md-12 d-flex align-items-center justify-content-between justify-content-md-end">
                        <div class="all-button-box mx-2">
                            <a href="{{ route('purchase.resent',$purchase->id) }}" class="btn btn-sm btn-primary">
                                {{__('Resend purchase')}}
                            </a>
                        </div>
                    <div class="all-button-box">
                        <a href="{{ route('purchase.pdf', Crypt::encrypt($purchase->id))}}" target="_blank" class="btn btn-sm btn-primary">
                            {{__('Download')}}
                        </a>
                    </div>
                </div>
            </div>
        @endif

    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="invoice">
                        <div class="invoice-print">
                            <div class="row invoice-title mt-2">
                                <div class="col-xs-12 col-sm-12 col-nd-6 col-lg-6 col-12">
                                    <h4>{{__('Purchase')}}</h4>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-nd-6 col-lg-6 col-12 text-end">
                                    <h4 class="invoice-number">{{ \Modules\Pos\Entities\Purchase::purchaseNumberFormat($purchase->purchase_id) }}</h4>
                                </div>
                                <div class="col-12">
                                    <hr>
                                </div>
                            </div>



                            <div class="row">
                                <div class="col text-end">
                                    <div class="d-flex align-items-center justify-content-end">
                                        <div class="me-4">
                                            <small>
                                                <strong>{{__('Issue Date')}} :</strong><br>
                                                {{company_date_formate($purchase->purchase_date)}}<br><br>
                                            </small>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                    <div class="col">
                                        <small class="font-style">
                                            <strong>{{__('Billed To')}} :</strong><br>

                                            @if(!empty($purchase->vender_name))
                                                {{ (!empty( $purchase->vender_name)?$purchase->vender_name:'') }}
                                            @else
                                            {{ !empty($vendor->billing_name) ? $vendor->billing_name : '' }}<br>
                                            {{ !empty($vendor->billing_address) ? $vendor->billing_address : '' }}<br>
                                            {{ !empty($vendor->billing_city) ? $vendor->billing_city . ' ,' : '' }}
                                            {{ !empty($vendor->billing_state) ? $vendor->billing_state . ' ,' : '' }}
                                            {{ !empty($vendor->billing_zip) ? $vendor->billing_zip : '' }}<br>
                                            {{ !empty($vendor->billing_country) ? $vendor->billing_country : '' }}<br>
                                            {{ !empty($vendor->billing_phone) ? $vendor->billing_phone : '' }}<br>
                                                <strong>{{__('Tax Number ')}} : </strong>{{!empty($vendor->tax_number)?$vendor->tax_number:''}}
                                            @endif
                                        </small>
                                    </div>
                                @if( company_setting('shipping_display')=='on')
                                    <div class="col">
                                        <small>
                                            <strong>{{__('Shipped To')}} :</strong><br>
                                            {{ !empty($vendor->shipping_name) ? $vendor->shipping_name : '' }}<br>
                                            {{ !empty($vendor->shipping_address) ? $vendor->shipping_address : '' }}<br>
                                            {{ !empty($vendor->shipping_city) ? $vendor->shipping_city .' ,': '' }}
                                            {{ !empty($vendor->shipping_state) ? $vendor->shipping_state .' ,': '' }}
                                            {{ !empty($vendor->shipping_zip) ? $vendor->shipping_zip : '' }}<br>
                                            {{ !empty($vendor->shipping_country) ? $vendor->shipping_country : '' }}<br>
                                            {{ !empty($vendor->shipping_phone) ? $vendor->shipping_phone : '' }}<br>
                                            <strong>{{__('Tax Number ')}} : </strong>{{!empty($vendor->tax_number)?$vendor->tax_number:''}}

                                        </small>
                                    </div>
                                @endif
                                <div class="col">
                                    <div class="float-end mt-3">
                                        {!! DNS2D::getBarcodeHTML(route('purchase.link.copy',\Illuminate\Support\Facades\Crypt::encrypt($purchase->id)), "QRCODE",2,2) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col">
                                    <small>
                                        <strong>{{__('Status')}} :</strong><br>
                                        @if($purchase->status == 0)
                                            <span class="badge bg-secondary p-2 px-3 rounded">{{ __(\Modules\Pos\Entities\Purchase::$statues[$purchase->status]) }}</span>
                                        @elseif($purchase->status == 1)
                                            <span class="badge bg-warning p-2 px-3 rounded">{{ __(\Modules\Pos\Entities\Purchase::$statues[$purchase->status]) }}</span>
                                        @elseif($purchase->status == 2)
                                            <span class="badge bg-danger p-2 px-3 rounded">{{ __(\Modules\Pos\Entities\Purchase::$statues[$purchase->status]) }}</span>
                                        @elseif($purchase->status == 3)
                                            <span class="badge bg-info p-2 px-3 rounded">{{ __(\Modules\Pos\Entities\Purchase::$statues[$purchase->status]) }}</span>
                                        @elseif($purchase->status == 4)
                                            <span class="badge bg-success p-2 px-3 rounded">{{ __(\Modules\Pos\Entities\Purchase::$statues[$purchase->status]) }}</span>
                                        @endif
                                    </small>
                                </div>
                                @if(!empty($customFields) && count($purchase->customField)>0)
                                @foreach($customFields as $field)
                                <div class="col text-md-end">
                                    <small>
                                        <strong>{{$field->name}} :</strong><br>
                                                {{!empty($purchase->customField[$field->id])?$purchase->customField[$field->id]:'-'}}
                                                <br><br>
                                            </small>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <div class="font-weight-bold">{{__('Item Summary')}}</div>
                                    <small class="mb-2">{{__('All items here cannot be deleted.')}}</small>
                                    <div class="table-responsive mt-2">

                                        <table class="table mb-0 table-striped">
                                                <tr>
                                                    <th class="text-dark" data-width="40">#</th>
                                                    <th class="text-dark">{{__('Item Type')}}</th>
                                                    <th class="text-dark">{{__('Item')}}</th>
                                                    <th class="text-dark">{{__('Quantity')}}</th>
                                                    <th class="text-dark">{{__('Rate')}}</th>
                                                    <th class="text-dark">{{__('Discount')}} </th>
                                                    <th class="text-dark">{{__('Tax')}}</th>
                                                    <th class="text-dark">{{__('Description')}}</th>
                                                    <th class="text-end text-dark" width="12%">{{__('Price')}}<br>
                                                        <small class="text-danger font-weight-bold">{{__('After discount & tax')}}</small>
                                                    </th>

                                                </tr>
                                                @php
                                                    $totalQuantity=0;
                                                    $totalRate=0;
                                                    $totalTaxPrice=0;
                                                    $totalDiscount=0;
                                                    $taxesData=[];
                                                    $TaxPrice_array = [];
                                                @endphp

                                                @foreach($iteams as $key =>$iteam)
                                                    @if(!empty($iteam->tax))
                                                        @php
                                                            $taxes=Modules\Pos\Entities\Purchase::taxs($iteam->tax);
                                                            $totalQuantity+=$iteam->quantity;
                                                            $totalRate+=$iteam->price;
                                                            $totalDiscount+=$iteam->discount;
                                                            foreach($taxes as $taxe){
                                                                $taxDataPrice=Modules\Pos\Entities\Purchase::taxRate($taxe->rate,$iteam->price,$iteam->quantity,$iteam->discount);
                                                                if (array_key_exists($taxe->name,$taxesData))
                                                                {
                                                                    $taxesData[$taxe->name] = $taxesData[$taxe->name]+$taxDataPrice;
                                                                }
                                                                else
                                                                {
                                                                    $taxesData[$taxe->name] = $taxDataPrice;
                                                                }
                                                            }
                                                        @endphp
                                                    @endif
                                                    <tr>
                                                        <td>{{$key+1}}</td>
                                                        <td>{{!empty($iteam->product_type) ? Str::ucfirst($iteam->product_type) : '--'}}</td>
                                                        <td>{{!empty($iteam->product())?$iteam->product()->name:''}}</td>
                                                        <td>{{$iteam->quantity}}</td>
                                                        <td>{{currency_format_with_sym($iteam->price)}}</td>
                                                        <td>{{currency_format_with_sym($iteam->discount)}}</td>
                                                        <td>
                                                            @if(!empty($iteam->tax))
                                                                <table>
                                                                    @php
                                                                        $totalTaxRate = 0;
                                                                        $data=0;
                                                                    @endphp
                                                                    @foreach($taxes as $tax)
                                                                        @php
                                                                            $taxPrice=Modules\Pos\Entities\Purchase::taxRate($tax->rate,$iteam->price,$iteam->quantity,$iteam->discount);
                                                                            $totalTaxPrice+=$taxPrice;
                                                                            $data+=$taxPrice;
                                                                        @endphp
                                                                        <tr>
                                                                            <td class="">{{$tax->name .' ('.$tax->rate .'%)'}}</td>
                                                                            <td>{{currency_format_with_sym($taxPrice)}}</td>
                                                                        </tr>
                                                                    @endforeach
                                                                    @php
                                                                        array_push($TaxPrice_array,$data);
                                                                    @endphp
                                                                </table>
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                        <td style="white-space: break-spaces;">{{!empty($iteam->description)?$iteam->description:'-'}}</td>
                                                        <td class="text-right">{{ currency_format_with_sym(($iteam->price * $iteam->quantity - $iteam->discount) + $totalTaxPrice)}}</td>
                                                    </tr>
                                                @endforeach
                                                <tfoot>
                                                <tr>
                                                    <td></td>
                                                    <td></td>
                                                    <td><b>{{__('Total')}}</b></td>
                                                    <td><b>{{$totalQuantity}}</b></td>
                                                    <td><b>{{currency_format_with_sym($totalRate)}}</b></td>
                                                    <td><b>{{currency_format_with_sym($totalDiscount)}}</b></td>
                                                    <td><b>{{currency_format_with_sym($totalTaxPrice)}}</b></td>

                                                </tr>
                                                <tr>
                                                    <td colspan="7"></td>
                                                    <td class="text-end"><b>{{__('Sub Total')}}</b></td>
                                                    <td class="text-end">{{currency_format_with_sym($purchase->getSubTotal())}}</td>
                                                </tr>

                                                    <tr>
                                                        <td colspan="7"></td>
                                                        <td class="text-end"><b>{{__('Discount')}}</b></td>
                                                        <td class="text-end">{{currency_format_with_sym($purchase->getTotalDiscount())}}</td>
                                                    </tr>

                                                @if(!empty($taxesData))
                                                    @foreach($taxesData as $taxName => $taxPrice)
                                                        <tr>
                                                            <td colspan="7"></td>
                                                            <td class="text-end"><b>{{$taxName}}</b></td>
                                                            <td class="text-end">{{ currency_format_with_sym($taxPrice) }}</td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                                <tr>
                                                    <td colspan="7"></td>
                                                    <td class="blue-text text-end"><b>{{__('Total')}}</b></td>
                                                    <td class="blue-text text-end">{{currency_format_with_sym($purchase->getTotal())}}</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="7"></td>
                                                    <td class="text-end"><b>{{__('Paid')}}</b></td>
                                                    <td class="text-end">{{currency_format_with_sym(($purchase->getTotal()-$purchase->getDue()))}}</td>
                                                </tr>

                                                <tr>
                                                    <td colspan="7"></td>
                                                    <td class="text-end"><b>{{__('Due')}}</b></td>
                                                    <td class="text-end">{{currency_format_with_sym($purchase->getDue())}}</td>
                                                </tr>
                                                </tfoot>
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
    </div>

@endsection
