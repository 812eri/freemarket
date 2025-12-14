@extends('layouts.app')

@section('title', '商品購入')
@section('css')
<link rel="stylesheet" href="{{ asset('css/purchase/show.css') }}">
@endsection

@section('content')
<div class="purchase-page container">
    <h1 class="page-title">商品購入</h1>

    <div class="purchase-main-content">
        <div class="purchase-left-section">

            <div class="item-summary-area border-bottom">
                <div class="item-summary-image">
                    <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="small-image">
                </div>

                <div class="item-summary-info">
                    <p class="item-summary-name">{{ $item->name }}</p>
                    <p class="item-summary-price">¥{{ number_format($item->price) }}</p>
                </div>
            </div>

            <div class="payment-method-section border-bottom">
                <h2 class="section-title">支払い方法</h2>
                <x-forms.select
                    id="paymentMethodSelect"
                    name="payment_method_display"
                    :options="[
                        'conbini' => 'コンビニ支払い',
                        'credit' => 'カード支払い']"
                        label=""
                    class="payment-select"
                    :selected="$selectedPaymentMethodCode ?? old('payment_method')"
                />

                @error('payment_method')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <div class="delivery-address-section border-bottom">
                <div class="section-header">
                    <h2 class="section-title">配送先</h2>
                    <a href="{{ route('address.edit', ['item_id' => $item->id, 'payment_method_code' => $selectedPaymentMethodCode ?? old('payment_method')]) }}" class="change-link">変更する</a>
                </div>

                <div class="current-address-info">
                    @if (isset($address) && $address->exists)
                        <p class="post-code">〒 {{ $address->post_code }}</p>
                        <p class="full-address">{{ $address->street_address . ($address->building_name ?? '') }}</p>
                    @else
                        <p class="warning-message">配送先が未設定です。設定してください。</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="purchase-right-section">
            <form method="post" action="{{ route('item.purchase', $item->id) }}" class="purchase-action-form">
                @csrf
                <input type="hidden" name="payment_method" id="hiddenPaymentMethod" value="{{ $selectedPaymentMethodCode ?? old('payment_method') }}">

                <div class="summary-box-wrapper">
                    <div class="summary-box">
                        <div class="summary-row summary-price-row">
                            <span class="label">商品代金</span>
                            <span class="price-value">¥{{ number_format($item->price) }}</span>
                        </div>

                        <div class="summary-row summary-payment-row-display">
                            <span class="label">支払い方法</span>
                            <span class="payment-value" id="displayedPaymentMethod">{{ $selectedPaymentMethod ?? '未選択' }}</span>
                        </div>
                    </div>
                </div>

                <div class="final-action-area">
                    <x-forms.button
                        type="submit"
                        variant="primary"
                        size="large"
                        class="purchase-button"
                    >
                        購入する
                    </x-forms.button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/purchase/purchase-page.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const wrapperOrSelect = document.getElementById('paymentMethodSelect');
        const changeAddressLink = document.querySelector('.change-link');

        if (wrapperOrSelect && changeAddressLink) {
            const paymentSelect = wrapperOrSelect.tagName === 'SELECT' 
                ? wrapperOrSelect 
                : wrapperOrSelect.querySelector('select');

            if (paymentSelect) {
                paymentSelect.addEventListener('change', function() {
                    const selectedValue = this.value;

                    const url = new URL(changeAddressLink.href);
                    url.searchParams.set('payment_method_code', selectedValue);

                    changeAddressLink.href = url.toString();
                });
            }
        }
    });
</script>
@endsection