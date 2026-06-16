<div class="col-md-4 mb-3">
    <div class="card h-100">
        <div class="card-header">
            <strong>{{ $titulo }}</strong>
        </div>

        <div class="card-body">

            <div class="form-group">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">Inicial</span>
                    </div>

                    <input
                        type="number"
                        min="1"
                        max="10000"
                        step="0.01"
                        name="{{ $prefixo }}min"
                        class="form-control"
                        value="{{ old($prefixo.'min', $tablet->{$prefixo.'min'}) }}"
                        required
                    >
                </div>
            </div>

            <div class="form-group">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">Atual</span>
                    </div>

                    <input
                        type="number"
                        min="1"
                        max="10000"
                        step="0.01"
                        name="{{ $prefixo }}med"
                        class="form-control"
                        value="{{ old($prefixo.'med', $tablet->{$prefixo.'med'}) }}"
                        required
                    >
                </div>
            </div>

            <div class="form-group mb-0">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">Máximo</span>
                    </div>

                    <input
                        type="number"
                        min="1"
                        max="10000"
                        step="0.01"
                        name="{{ $prefixo }}max"
                        class="form-control"
                        value="{{ old($prefixo.'max', $tablet->{$prefixo.'max'}) }}"
                        required
                    >
                </div>
            </div>

        </div>
    </div>
</div>