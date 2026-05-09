<?php

namespace App\Filament\Admin\Pages;

use App\Models\Tenant;
use App\Filament\Admin\Resources\TenantResource;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Artisan;

class RegisterTenant extends Page
{
    protected string $view = 'filament.admin.pages.register-tenant';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-plus-circle';

    protected static ?string $navigationLabel = 'Register New Shop';

    protected static ?string $title = 'Register New Shop';

    public ?string $shop_name = null;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Shop Registration')
                    ->description('Enter the shop name. The workspace ID and domain will be automatically generated from it.')
                    ->components([
                        TextInput::make('shop_name')
                            ->label('Shop Name')
                            ->required()
                            ->alpha()
                            ->placeholder('e.g. MalynExecutive')
                            ->helperText(fn () => $this->shop_name
                                ? 'Domain will be: ' . str($this->shop_name)->slug()->toString() . '.' . (config('tenancy.central_domains')[0] ?? 'localhost')
                                : 'Enter a name (letters only, no spaces or special characters).'
                            )
                            ->live(),
                    ]),
            ]);
    }

    public function getFormActions(): array
    {
        return [
            Action::make('create')
                ->label('Provision Shop Workspace')
                ->icon('heroicon-o-server-stack')
                ->submit('create'),
        ];
    }

    public function create(): void
    {
        $this->validate([
            'shop_name' => ['required', 'string', 'alpha', 'max:255'],
        ]);

        $slug = str($this->shop_name)->slug()->toString();

        if (Tenant::where('id', $slug)->exists()) {
            $this->addError('shop_name', "A workspace with the slug '{$slug}' already exists. Please choose a different shop name.");
            return;
        }

        try {
            $tenant = Tenant::create(['id' => $slug]);

            // Explicitly run all tenant migrations for this new shop
            Artisan::call('tenants:migrate', [
                '--tenants' => [$tenant->id],
            ]);

            // Run the tenant seeder (roles, default users, services, etc.)
            Artisan::call('tenants:seed', [
                '--tenants' => [$tenant->id],
            ]);

            Notification::make()
                ->title('Shop Registered Successfully')
                ->body("Workspace '{$slug}' ({$this->shop_name}) has been created, migrations run, and default data seeded.")
                ->success()
                ->send();

            $this->shop_name = null;

            $this->redirect(TenantResource::getUrl('index'));

        } catch (\Exception $e) {
            Notification::make()
                ->title('Registration Failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
