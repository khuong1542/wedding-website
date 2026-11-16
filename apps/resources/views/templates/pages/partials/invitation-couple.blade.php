            <section class="v2-couple v2-section" id="v2-couple">
                <h2>{{ __('pages.couple') }}</h2>
                <div class="v2-couple-grid">
                    @foreach (['groom' => $groomName, 'bride' => $brideName] as $person => $name)
                        <article class="v2-person">
                            <div class="skeleton-block v2-portrait" aria-hidden="true"></div>
                            <div>
                                <h3>{{ $name }}</h3>@include('templates.pages.partials.demo-lines', ['count' => 2])
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
