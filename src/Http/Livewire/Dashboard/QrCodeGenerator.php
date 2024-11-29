<?php

declare(strict_types=1);

namespace Revolution\Ordering\Http\Livewire\Dashboard;

use Livewire\Component;

class QrCodeGenerator extends Component
{
    public function render()
    {
        return view('ordering::livewire.dashboard.qr-code-generator');
    }
}
