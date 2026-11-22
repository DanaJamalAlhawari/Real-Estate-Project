<p class="checkout-note"><i class="bi bi-info-circle" aria-hidden="true"></i> Use the test card details to simulate a payment.</p>

<div class="checkout-card-fields">
    <div class="checkout-card-fields__number">
        <label class="form-label" for="demo-card-number">Card number</label>
        <input type="text" class="form-control @error('card_number') is-invalid @enderror" id="demo-card-number" name="card_number"
            inputmode="numeric" maxlength="19" required
            @error('card_number') aria-invalid="true" aria-describedby="demo-card-number-error" @enderror>
        @error('card_number')
            <div class="invalid-feedback" id="demo-card-number-error">{{ $message }}</div>
        @enderror
    </div>
    <div>
        <label class="form-label" for="demo-card-expiry">Expiration date</label>
        <input type="text" class="form-control @error('card_expiry') is-invalid @enderror" id="demo-card-expiry" name="card_expiry"
            maxlength="5" required
            @error('card_expiry') aria-invalid="true" aria-describedby="demo-card-expiry-error" @enderror>
        @error('card_expiry')
            <div class="invalid-feedback" id="demo-card-expiry-error">{{ $message }}</div>
        @enderror
    </div>
    <div>
        <label class="form-label" for="demo-card-cvv">Security code (CVV)</label>
        <input type="text" class="form-control @error('card_cvv') is-invalid @enderror" id="demo-card-cvv" name="card_cvv"
            inputmode="numeric" maxlength="3" required
            @error('card_cvv') aria-invalid="true" aria-describedby="demo-card-cvv-error" @enderror>
        @error('card_cvv')
            <div class="invalid-feedback" id="demo-card-cvv-error">{{ $message }}</div>
        @enderror
    </div>
</div>
