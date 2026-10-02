@props([
    'position',
    'pitch' => 'Sua marca neste espaço publicitário.',
])

<aside {{ $attributes->merge(['class' => 'w-full lg:w-80 shrink-0 lg:sticky lg:top-24']) }}>
    <x-ad-space :position="$position" variant="sidebar" :pitch="$pitch" />
</aside>
