@php
    // Credit-simulation params -> real JS numbers for calculator.js. Computed
    // ABOVE the <script> so the App.CREDIT assignment below is live JS (not
    // inert HTML after the tag). Fallbacks reproduce today's hardcoded behavior
    // when a key is missing/empty. rate is a 0..1 fraction (percent / 100).
    $settings = $settings ?? [];
    $creditRate = round(((($settings['credit_interest_rate'] ?? '') !== '') ? 0 + $settings['credit_interest_rate'] : 4) / 100, 6);
    $creditNum = fn ($key, $default) => 0 + ((($settings[$key] ?? '') !== '') ? $settings[$key] : $default);
@endphp
<script>
window.App = window.App || {};
App.CARS = @json($cars);
App.CAT_STYLE = @json($catStyle);
App.QUIZ = @json($quiz);
App.WHEEL_PRIZES = @json($wheelPrizes);
App.CORNER_IMAGES = @json($cornerImages);
App.HERO_SLIDES = @json($heroSlides);
App.TESTIMONIALS = @json($testimonials);
App.CREDIT = {
    rate: {!! json_encode($creditRate) !!},
    defaultDp: {!! json_encode($creditNum('credit_default_dp', 20)) !!},
    minDp: {!! json_encode($creditNum('credit_min_dp', 10)) !!},
    maxDp: {!! json_encode($creditNum('credit_max_dp', 50)) !!},
    dpStep: {!! json_encode($creditNum('credit_dp_step', 5)) !!},
    minTenor: {!! json_encode($creditNum('credit_min_tenor', 1)) !!},
    maxTenor: {!! json_encode($creditNum('credit_max_tenor', 6)) !!},
    defaultTenor: {!! json_encode($creditNum('credit_default_tenor', 4)) !!},
};
</script>
