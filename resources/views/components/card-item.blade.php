<div class="col-xl-6">
    <div class="card custom-card">
        <div class="card-body">
            <div class="row">
                <div class="col-2 my-auto">
                    <span class="settings-icon bg-primary-transparent text-primary border-primary">
                        <i class="nav-icon {{ $icon }}"></i>
                    </span>
                </div>
                <div class="col-10 d-flex justify-content-between">
                    <div class="my-auto">
                        <a href="{{ $url }}">
                            <h5 class="my-auto text-dark">{{ $title }}</h5>
                        </a>
                        <p class="my-auto">{{ $count }} &nbsp;{{ $description }}</p>
                    </div>
                    <div class="my-auto">
                        <a href="{{ $url }}">
                            <i class="fa fa-angle-double-right fs-20 my-auto"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
