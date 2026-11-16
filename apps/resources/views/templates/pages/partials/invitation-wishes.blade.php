            <section class="v2-wishes v2-section" id="v2-wishes">
                <h2>{{ __('pages.wishes') }}</h2>
                <div class="v2-wishes-layout" aria-hidden="true">
                    <div class="v2-wish-list">
                        @for ($wish = 0; $wish < 3; $wish++)
                            <article><span class="skeleton-block v2-avatar"></span>@include('templates.pages.partials.demo-lines', ['count' => 2])</article>
                        @endfor
                    </div>
                    <div class="v2-demo-form">
                        @for ($input = 0; $input < 2; $input++)
                            <span class="skeleton-block v2-form-label"></span><span
                                class="skeleton-block v2-form-input"></span>
                        @endfor
                        <span class="skeleton-block v2-form-textarea">
                        </span><span class="skeleton-block v2-button-placeholder"></span>
                    </div>
                </div>
            </section>
