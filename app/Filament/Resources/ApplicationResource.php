<?php
namespace App\Filament\Resources;

use App\Filament\Resources\ApplicationResource\Pages;
use App\Models\Application;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\Wizard;
use Illuminate\Support\Facades\Storage;

class ApplicationResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-8-tooth';
    protected static ?string $navigationLabel = 'APPLICATION SETUP';
    protected static ?string $modelLabel = 'Application';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Logo')
                        ->schema([
                            Forms\Components\FileUpload::make('logo')
                                ->label('')
                                ->avatar()
                                ->image()
                                ->imageEditor()
                                ->imageEditorEmptyFillColor('#000000')
                                ->directory('uploads/logos')  // Specify the directory for uploads
                        ]),
                    Wizard\Step::make('Application Name & Licence')
                        ->schema([
                            Forms\Components\TextInput::make('name')->required(),
                            Forms\Components\TextInput::make('licence_number'),
                        ]),
                    Wizard\Step::make('Details')
                        ->schema([
                            Forms\Components\TextInput::make('description'),
                            Forms\Components\TextInput::make('instructions'),
                        ]),
                ]),
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\ImageColumn::make('logo')
                    ->url(fn($record) => Storage::url($record->logo)),  // Ensure the correct URL is used
                Tables\Columns\TextColumn::make('description'),
                Tables\Columns\TextColumn::make('licence_number'),
                Tables\Columns\TextColumn::make('instructions'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListApplications::route('/'),
            'create' => Pages\CreateApplication::route('/create'),
            'view' => Pages\ViewApplication::route('/{record}'),
            'edit' => Pages\EditApplication::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return Application::count() === 0;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function getDefaultNavigationUrl(): string
    {
        $application = Application::first();
        if (!$application) {
            return self::getUrl('create');
        }
        return self::getUrl('edit', $application);
    }
}
