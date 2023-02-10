@if(count($combinations[0]) > 0)
    <table class="table table-bordered">
        <thead>
        <tr>
            <td class="text-center">
                <label for="" class="control-label">Variant</label>
            </td>
            <td class="text-center">
                <label for="" class="control-label">Variant Price</label>
            </td>
            <td class="text-center">
                <label for="" class="control-label">Original Price (Without Discount)</label>
            </td>
            <td class="text-center">
                <label for="" class="control-label">Variant Stock</label>
            </td>
            <td class="text-center">
                <label for="" class="control-label">Bar Code</label>
            </td>
            <td class="text-center">
                <label for="" class="control-label">Discount</label>
            </td>
            <td class="text-center">
                <label for="" class="control-label">Discount Type</label>
            </td>
        </tr>
        </thead>
        <tbody>

        @foreach ($combinations as $key => $combination)
            @php
                $str = '';
                foreach ($combination as $key => $item){
                    if($key > 0 ){
                        $str .= '-'.str_replace(' ', '', $item);
                    }
                    else{
                        $str .= str_replace(' ', '', $item);
                    }
                }
            @endphp
            @if(strlen($str) > 0)
                <tr>
                    <td>
                        <label for="" class="control-label">{{ $str }}</label>
                     </td>
                    <td>
                        <input type="number" name="price_{{ $str }}" value="{{ $price }}" min="0" step="0.01"
                               class="form-control" required>
                    </td>
                    <td>
                        <input type="number" name="org_price_{{ $str }}" value="{{ $price }}" min="0" step="0.01"
                               class="form-control" required>
                    </td>
                    <td>
                        <input type="number" name="stock_{{ $str }}" value="0" min="0" max="1000000"
                               class="form-control" onkeyup="update_qty()" required>
                    </td>
                    <td>
                        <input type="text" name="barcode_{{ $str }}" value="" class="form-control" required>
                    </td>
                    <td>
                        <input type="text" name="discount_{{ $str }}" value="" class="form-control" required>
                    </td>
                    <td>
                        <select name="discount_type_{{ $str }}" class="form-control js-select2-custom">
                            <option value="">Select Discount Type</option>
                            <option value="percent">Percent</option>
                            <option value="amount">Amount</option>
                        </select>
                    </td>
                </tr>
            @endif
        @endforeach
        </tbody>
    </table>
@endif
