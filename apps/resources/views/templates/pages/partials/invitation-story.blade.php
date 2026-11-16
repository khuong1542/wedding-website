            <section class="v2-story v2-section" id="v2-story">
                <h2>{{ __('pages.story') }}</h2>
                <div class="v2-timeline" aria-hidden="true">
                    @for ($milestone = 0; $milestone < 4; $milestone++)
                        <article><span class="v2-milestone">♡</span>@include('templates.pages.partials.demo-lines', ['count' => 2])</article>
                    @endfor
                </div>
            </section>
