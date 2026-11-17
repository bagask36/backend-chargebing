<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Models\ChargingApp;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Form;

class ChargingAppForm extends Form
{
    public ?ChargingApp $chargingApp = null;

    public string $name = '';

    public string $slug = '';

    public string $scheme_url = '';

    public string $app_store_url = '';

    public string $play_store_url = '';

    public string $icon_url = '';

    public bool $is_active = true;

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('charging_apps', 'slug')->ignore($this->chargingApp?->id),
            ],
            'scheme_url' => ['nullable', 'string', 'max:255'],
            'app_store_url' => ['nullable', 'string', 'max:1000'],
            'play_store_url' => ['nullable', 'string', 'max:1000'],
            'icon_url' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
        ];
    }

    public function setChargingApp(ChargingApp $app): void
    {
        $this->chargingApp = $app;
        $this->name = $app->name;
        $this->slug = $app->slug;
        $this->scheme_url = (string) ($app->scheme_url ?? '');
        $this->app_store_url = (string) ($app->app_store_url ?? '');
        $this->play_store_url = (string) ($app->play_store_url ?? '');
        $this->icon_url = (string) ($app->icon_url ?? '');
        $this->is_active = (bool) $app->is_active;
    }

    public function resetForm(): void
    {
        $this->chargingApp = null;
        $this->name = '';
        $this->slug = '';
        $this->scheme_url = '';
        $this->app_store_url = '';
        $this->play_store_url = '';
        $this->icon_url = '';
        $this->is_active = true;
    }

    public function store(): ChargingApp
    {
        return ChargingApp::create($this->prepareData());
    }

    public function update(): void
    {
        if (! $this->chargingApp) {
            return;
        }

        $this->chargingApp->update($this->prepareData());
    }

    /**
     * @return array<string, mixed>
     */
    protected function prepareData(): array
    {
        $slug = trim($this->slug);
        if (empty($slug)) {
            $slug = Str::slug($this->name);
        }

        return [
            'name' => trim($this->name),
            'slug' => $slug,
            'scheme_url' => filled($this->scheme_url) ? trim($this->scheme_url) : null,
            'app_store_url' => filled($this->app_store_url) ? trim($this->app_store_url) : null,
            'play_store_url' => filled($this->play_store_url) ? trim($this->play_store_url) : null,
            'icon_url' => filled($this->icon_url) ? trim($this->icon_url) : null,
            'is_active' => $this->is_active,
        ];
    }
}
