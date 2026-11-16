            <section class="v2-events v2-section" id="v2-events">
                <h2>{{ __('pages.events') }}</h2>@include('templates.pages.partials.demo-lines', ['count' => 1])<div class="v2-event-grid"
                    aria-hidden="true">
                    @for ($event = 0; $event < 3; $event++)
                        <article><span class="skeleton-block v2-event-image"></span>@include('templates.pages.partials.demo-lines', ['count' => 3])<span
                                class="skeleton-block v2-button-placeholder"></span></article>
                    @endfor
                </div>
            </section>
