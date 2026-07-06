<div class="workflow-stepper">
    @php
        $steps = [
            ['id' => 1, 'label' => 'Checks'],
            ['id' => 2, 'label' => 'Freeze'], 
            ['id' => 3, 'label' => 'Process'],
            ['id' => 4, 'label' => 'Verify'],
            ['id' => 5, 'label' => 'Done'],
        ];
    @endphp
    
    @foreach($steps as $index => $step)
    <div class="stepper-item">
        <div class="stepper-content {{ $index !== count($steps) - 1 ? 'w-20 md:w-32' : '' }}">
            <div class="stepper-circle 
                {{ $step['id'] < $currentStep ? 'completed' : '' }}
                {{ $step['id'] == $currentStep ? 'active' : '' }}
                {{ $step['id'] > $currentStep ? 'inactive' : '' }}"
                data-step="{{ $step['id'] }}"
                title="Go to {{ $step['label'] }}"
            >
                @if($step['id'] < $currentStep)
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                @else
                    {{ $step['id'] }}
                @endif
            </div>
            <span class="stepper-label 
                {{ $step['id'] <= $currentStep ? 'active' : 'inactive' }}">
                {{ $step['label'] }}
            </span>
            
            @if($index !== count($steps) - 1)
            <div class="stepper-connector 
                {{ $step['id'] < $currentStep ? 'active' : 'inactive' }}"></div>
            @endif
        </div>
    </div>
    @endforeach
</div>