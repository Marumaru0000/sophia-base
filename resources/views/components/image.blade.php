@props(['src'])

<img src="{{ is_array($src) ? ($src['url'] ?? config('ordering.menu.no_image')) : ($src ?? config('ordering.menu.no_image')) }}"
     {{ $attributes->merge(['class' => 'rounded-md h-24 object-contain object-right-top']) }}
     alt="">