@props(['title', 'value', 'icon', 'color' => 'primary'])

<div class="card card-hover border-start border-{{ $color }} border-5 shadow-sm">
    <div class="card-body d-flex align-items-center justify-content-between">
        <div>
            <h6 class="text-muted mb-2">{{ $title }}</h6>
            <h4 class="fw-bold mb-0">{{ $value }}</h4>
        </div>
        <div class="avatar bg-{{ $color }} text-white">
            <i class="bi {{ $icon }} fs-4"></i>
        </div>
    </div>
</div>
