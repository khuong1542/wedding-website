            <section class="v2-gifts v2-section">
                <h2>{{ __('pages.registry') }}</h2>@include('templates.pages.partials.demo-lines', ['count' => 2])<div class="v2-gift-grid"
                    aria-hidden="true">
                    @for ($gift = 0; $gift < 2; $gift++)
                        <article><span class="skeleton-block v2-qr"></span>@include('templates.pages.partials.demo-lines', ['count' => 2])</article>
                    @endfor
                </div>
            </section>
