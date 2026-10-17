@php
    use App\Support\RouteMap;
    use Illuminate\Support\Str;

    // "data.itinerary.<uuid>.map_picker" → "data.itinerary.<uuid>"
    $itemPath = Str::beforeLast($getStatePath(), '.');
    $livewire = $getLivewire();
    $itemKey = Str::afterLast($itemPath, '.');
    $item = (array) data_get($livewire, $itemPath, []);
    $itinerary = array_filter((array) data_get($livewire, 'data.itinerary', []), 'is_array');

    $here = RouteMap::resolve($item);
    $others = [];
    $number = false;
    foreach (array_keys($itinerary) as $i => $key) {
        if ((string) $key === $itemKey) {
            $number = $i;

            continue;
        }
        if ($point = RouteMap::resolve($itinerary[$key])) {
            $others[] = ['n' => $i + 1, 'name' => $point['name'], 'lat' => $point['lat'], 'lng' => $point['lng']];
        }
    }

    $config = [
        'path' => $itemPath,
        'number' => $number === false ? null : $number + 1,
        'here' => $here ? ['name' => $here['name'], 'lat' => $here['lat'], 'lng' => $here['lng']] : null,
        'others' => $others,
        'tiles' => RouteMap::tiles(),
    ];
@endphp

<div
    wire:key="map-picker-{{ md5($itemPath.json_encode($config)) }}"
    wire:ignore
    x-data="myMapPicker(@js($config))"
    class="sr-map-picker"
>
    <div x-ref="map" class="sr-map-picker__map" role="application" aria-label="Map: click or drag the pin to set where this day is"></div>
    <p class="sr-map-picker__hint">
        Click the map or drag the pin to set an exact spot (switches to “Other place”). Grey pins are the other days.
    </p>
</div>
