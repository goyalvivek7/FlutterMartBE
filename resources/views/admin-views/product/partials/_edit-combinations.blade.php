@if(count($combinations) > 0)
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
            <tr>
                <td>
                    <label for="" class="control-label">{{ $combination['type'] }}</label>
                </td>
                <td>
                    <input type="number" name="price_{{ $combination['type'] }}"
                           value="{{$combination['price']}}" min="0"
                           step="0.01"
                           class="form-control" required>
                </td>
                <td>
                    <input type="number" name="org_price_{{ $combination['type'] }}" value="<?php if(isset($combination['org_price'])){ echo $combination['org_price']; } ?>" min="0" step="0.01"
                            class="form-control" required>
                </td>
                <td>
                    <input type="number" name="stock_{{ $combination['type'] }}" value="{{ $combination['stock']??0 }}"
                           min="0" max="1000000" onkeyup="update_qty()"
                           class="form-control" required>
                </td>
                <td>
                    <input type="text" name="barcode_{{ $combination['type'] }}" value="{{ $combination['barcode']??'' }}"
                           class="form-control" required>
                </td>
                <td>
                    <input type="text" name="discount_{{ $combination['type'] }}" value="{{ $combination['discount']??'' }}"
                           class="form-control" required>
                </td>
                <td>
                    <select name="discount_type_{{ $combination['type'] }}" class="form-control js-select2-custom">
                        <option value="">Select Discount Type</option>
                        <option value="percent" <?php if(isset($combination['discount_type']) && $combination['discount_type']=='percent'){ echo 'selected'; } ?>>
                            Percent
                        </option>
                        <option value="amount" <?php if(isset($combination['discount_type']) && $combination['discount_type']=='amount'){ echo 'selected'; } ?>>
                            Amount
                        </option>
                    </select>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif
