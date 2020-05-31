@extends('layui.layouts.default')
@section('notice')
    @include('layui.layouts._notice')
@endsection
@section('content')
<script>
    document.title = '商品购买 - '+document.title;  
</script>
    <div class="layui-container cardcon" style="left:0px;">
        <hr>
        <div class="layui-form layui-form-item">
            <label class="layui-form-label">分类筛选</label>
            <div style="position: relative;display: inline-block;*display: inline;*zoom: 1;vertical-align: middle;line-height: 60px;width:200px">
                <select class="classifys" lay-filter="classifys">
                    <option value="">请选择分类</option>
                    @foreach($classifys as $classify)
                        <option value="{{ $classify['name'] }}">{{ $classify['name'] }}</option>
                    @endforeach
                </select></div>
        </div>
        <hr>
    </div>
    @foreach($classifys as $classify)

        <!--电脑端-->
        <div class="layui-row layui-hide-xs category">
            <div class="layui-container">
                <div class="layui-card cardcon">
                    <div class="layui-card-header">{{ $classify['name'] }}</div>
                    <div class="layui-card-body">

                        <table class="layui-table">
                            <colgroup>
                                <col>
                                <col width="100">
                                <col width="80">
                                <col width="80">
                                <col width="80">
                                <col width="120">
                            </colgroup>
                            <thead>
                            <tr>
                                <th>商品名称</th>
                                <th>发货模式</th>
                                <th>库存</th>
                                <th>销量</th>
                                <th>单价</th>
                                <th style="text-align: center !important;">操作</th>
                            </tr>
                            </thead>
                            <tbody class="layui-collapse">
                            <style type="text/css">
                                tr.layui-colla-item i {
                                    top: -12px;
                                    float: left;
                                    margin: 0;
                                    padding: 0;
                                    left: 18px !important
                                }

                                strong.layui-colla-title {
                                    background: none;
                                    font-weight: normal;
                                }
                            </style>
                            @foreach($classify['products'] as $product)
                                <tr class="layui-colla-item product">
                                    <td class="layui-hide">{{ $classify['name'] }}-{{ $product['pd_name'] }}</td>
                                    <td><strong class="layui-colla-title">{{ $product['pd_name'] }}
                                            @if($product['wholesale_price'])
                                                &nbsp<span class="layui-badge layui-bg-blue">折扣</span>
                                            @endif
                                            <i class="layui-icon layui-colla-icon"></i></strong>
                                        <div class="layui-colla-content">
                                            {!! $product['pd_info'] !!}
                                        </div>
                                    </td>
                                    <td>
                                        @if($product['pd_type'] == 1)
                                            <span class="layui-badge layui-bg-black">自动发货</span>
                                        @else
                                            <span class="layui-badge layui-bg-gray">人工发货</span>
                                        @endif
                                    </td>
                                    <td>{{ $product['in_stock'] }}</td>
                                    <td>{{ $product['sales_volume'] }}</td>
                                    <td>￥{{ $product['actual_price'] }}</td>
                                    <td align="center">
                                        @if($product['in_stock'] > 0)
                                            <a href="{{ url("/buy/{$product['id']}") }}"
                                               class="layui-btn  layui-btn-sm layui-btn-normal">购买<i
                                                        class="layui-icon layui-icon-cart"></i></a>
                                        @else
                                            <a href="#" class="layui-btn  layui-btn-sm layui-btn-disabled">购买<i
                                                        class="layui-icon layui-icon-cart"></i></a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach

                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>

        <!--移动端-->
        <div class="layui-row layui-hide-md">
            <div class="layui-container">
                <div class="layui-card cardcon category">
                    <div class="layui-card-header">{{ $classify['name'] }}</div>
                    <div class="layui-card-body">
                        <div class="layui-row">
                            @foreach($classify['products'] as $product)
                                <div class="layui-card-body product">
                                    <a href="{{ url("/buy/{$product['id']}") }}">
                                        <div class="layui-col-md3 layui-col-sm4 goodsdetail">
                                            <div class="goodsdetail-mobile">
                                                <div class="goodsdetail-mobile-img">
                                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('admin')->url($product['pd_picture']) }}">
                                                </div>
                                                <div class="layui-hide">{{ $classify['name'] }}
                                                    -{{ $product['pd_name'] }}</div>
                                                <div class="goodsdetail-mobile-text">
                                                    <p class="title">{{ $product['pd_name'] }}</p>
                                                    <p class="biaozhi">
                                                        <span class="price"><b>￥{{ $product['actual_price'] }}</b></span>
                                                        @if($product['pd_type'] == 1)
                                                            <span class="layui-badge layui-bg-black">自动发货</span>
                                                        @else
                                                            <span class="layui-badge layui-bg-gray">人工发货</span>
                                                        @endif
                                                        @if($product['wholesale_price'])
                                                            &nbsp<span class="layui-badge layui-bg-blue">折扣</span>
                                                        @endif
                                                    </p>
                                                    <p>
                                                        <span>库存:{{$product['in_stock']}}&nbsp|&nbsp销量:{{ $product['sales_volume'] }}&nbsp</span>
                                                    </p>
                                                    <div class="goodsdetail-mobile-description">
                                                        <p></p>
                                                        <footer style="text-align: center;"></footer>
                                                        <p></p>
                                                        <div class="product-desc"
                                                             style="text-align: start;">{!! $product['pd_info'] !!}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </a>

                                </div>

                            @endforeach

                        </div>

                    </div>
                </div>
            </div>
        </div>

    @endforeach
    <div id="layerad" style="display: none;">{!! config('webset.layerad') !!}</div>
@stop