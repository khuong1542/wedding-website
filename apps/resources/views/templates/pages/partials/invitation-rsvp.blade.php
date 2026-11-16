            <section class="v2-rsvp v2-section">
                <h2>{{ __('pages.version2_rsvp') }}</h2>@include('templates.pages.partials.demo-lines', ['count' => 1])<div class="v2-demo-form"
                    aria-hidden="true">
                    @for ($field = 0; $field < 3; $field++)
                        <div><span class="skeleton-block v2-form-label"></span><span
                                class="skeleton-block v2-form-input"></span></div>
                    @endfor
                    <span class="skeleton-block v2-form-textarea">
                    </span><span class="skeleton-block v2-button-placeholder"></span>
                </div>
            </section>
