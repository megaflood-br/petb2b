@props(['model', 'class' => 'absolute top-3 right-3 z-20', 'context' => 'card'])

<div {{ $attributes->class($class) }} onclick="event.preventDefault(); event.stopPropagation();">
    <livewire:favorite-button
        :favoritable="$model"
        :wire:key="'fav-'.$context.'-'.$model->getMorphClass().'-'.$model->getKey()" />
</div>
