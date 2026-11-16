            <section class="v2-welcome v2-section">
                <h2>{{ __('pages.version2_welcome') }}</h2>@include('templates.pages.partials.demo-lines', ['count' => 2])<div class="v2-countdown"
                    aria-hidden="true">
                    @for ($unit = 0; $unit < 4; $unit++)
                        <div><span class="skeleton-block v2-countdown-number"></span><span
                                class="skeleton-block v2-line"></span></div>
                    @endfor
                </div>
            </section>
