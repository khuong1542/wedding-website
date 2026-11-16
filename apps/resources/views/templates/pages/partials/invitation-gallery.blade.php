            <section class="v2-gallery v2-section" id="v2-gallery">
                <h2>{{ __('pages.gallery') }}</h2>
                <div class="v2-gallery-filters" aria-hidden="true">
                    @for ($filter = 0; $filter < 4; $filter++)
                        <span class="skeleton-block"></span>
                    @endfor
                </div>
                <div class="v2-gallery-grid" aria-hidden="true">
                    @for ($photo = 0; $photo < 6; $photo++)
                        <span class="skeleton-block"></span>
                    @endfor
                </div>
            </section>
