<div class="space-y-3">
    @include('filament-widgets::chart-widget')

    <div class="flex justify-end">
        <x-filament::button
            :href="$this->getDetailsUrl()"
            tag="a"
            icon="heroicon-o-list-bullet"
            color="gray"
        >
            Ver detalle de asistencia
        </x-filament::button>
    </div>
</div>
