<?php

namespace App\Filament\Pages;

use App\Filament\Resources\SocialLinkResource;
use App\Models\FooterSetting;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page as BasePage;
use Illuminate\Support\HtmlString;

/**
 * Seluruh isi footer dalam satu layar.
 *
 * Footer adalah satu-satunya bagian situs yang tampil di SETIAP halaman, tetapi
 * isinya dulu tersebar ke tempat-tempat yang tidak masuk akal: copyright di
 * dalam record halaman Beranda, label "Ikuti kami" di berkas bahasa, dan kedua
 * logo ditulis langsung di JSX. Editor yang ingin mengganti logo tidak punya
 * satu pun layar untuk dituju.
 *
 * Memakai Page, bukan Resource: datanya satu baris, jadi daftar berisi satu
 * baris beserta tombol "Buat" dan "Hapus" hanya akan menyesatkan.
 */
class Footer extends BasePage implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.pages.footer';

    protected static ?string $navigationGroup = 'Halaman';

    protected static ?string $navigationLabel = 'Footer';

    protected static ?int $navigationSort = 8;

    protected static ?string $navigationIcon = 'heroicon-o-bars-3-bottom-left';

    protected static ?string $title = 'Footer';

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function getSubheading(): string
    {
        return 'Tampil di bagian bawah setiap halaman, dalam kedua bahasa. Tautan menu di footer mengikuti menu navigasi, bukan layar ini.';
    }

    public function mount(): void
    {
        $this->form->fill(FooterSetting::current()->attributesToArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->columns(3)
            ->statePath('data')
            ->schema([
                Forms\Components\Group::make()
                    ->columnSpan(['lg' => 2])
                    ->schema([
                        Forms\Components\Section::make('Teks')
                            ->description('Label ajakan media sosial di kiri bawah dan teks copyright di tengah.')
                            ->schema([
                                Forms\Components\Tabs::make('Bahasa')
                                    ->tabs([
                                        Forms\Components\Tabs\Tab::make('Bahasa Indonesia')
                                            ->schema(static::contentFields('id')),
                                        Forms\Components\Tabs\Tab::make('English')
                                            ->schema(static::contentFields('en')),
                                    ])
                                    ->columnSpanFull(),
                            ]),
                    ]),

                Forms\Components\Group::make()
                    ->columnSpan(['lg' => 1])
                    ->schema([
                        Forms\Components\Section::make('Logo (kanan bawah)')
                            ->description('Tampil di kanan bawah footer, dan di bagian atas menu mobile.')
                            ->schema([
                                Forms\Components\Repeater::make('logos')
                                    ->hiddenLabel()
                                    ->addActionLabel('Tambah Logo')
                                    ->reorderable()
                                    ->collapsible()
                                    ->defaultItems(0)
                                    ->itemLabel(fn (array $state): ?string => $state['alt'] ?? null)
                                    ->schema([
                                        Forms\Components\FileUpload::make('image')
                                            ->label('Berkas')
                                            ->helperText('Latar footer berwarna ungu — gunakan logo putih (SVG atau PNG transparan).')
                                            ->image()
                                            ->directory('footer')
                                            ->required(),
                                        Forms\Components\TextInput::make('alt')
                                            ->label('Teks alternatif')
                                            ->helperText('Dibacakan pembaca layar dan tampil bila gambar gagal dimuat.')
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('height')
                                            ->label('Tinggi pada layar lebar (px)')
                                            ->helperText('Ukuran desain: Combiphar 82, Combi Care Center 89. Ikut mengecil di layar sempit.')
                                            ->numeric()
                                            ->minValue(16)
                                            ->maxValue(200)
                                            ->default(82),
                                    ]),
                            ]),

                        Forms\Components\Section::make('Ikon media sosial')
                            ->schema([
                                Forms\Components\Placeholder::make('socials')
                                    ->hiddenLabel()
                                    ->content(new HtmlString(
                                        'Ikon bulat di kiri bawah footer dikelola di menu <a class="font-semibold text-primary-600 hover:underline dark:text-primary-400" href="'
                                        .SocialLinkResource::getUrl()
                                        .'">Pengaturan &rarr; Media Sosial</a>, karena ikon yang sama juga dipakai di popup produk.'
                                    )),
                            ]),
                    ]),
            ]);
    }

    /** Teks footer untuk satu bahasa. */
    private static function contentFields(string $locale): array
    {
        $isId = $locale === 'id';

        return [
            Forms\Components\TextInput::make("follow_label_{$locale}")
                ->label($isId ? 'Label ikuti kami' : 'Follow us label')
                ->helperText($isId ? 'Kosongkan untuk menyembunyikan teksnya — ikonnya tetap tampil.' : 'Leave empty to hide the text — the icons still show.')
                ->maxLength(255)
                ->columnSpanFull(),
            Forms\Components\RichEditor::make("copyright_{$locale}")
                ->label($isId ? 'Teks copyright' : 'Copyright text')
                ->helperText($isId ? 'Gunakan tebal untuk menyorot nama Combiphar. Kosongkan untuk menyembunyikan baris ini.' : 'Use bold to highlight the Combiphar name. Leave empty to hide this line.')
                ->toolbarButtons(['bold', 'italic', 'link', 'undo', 'redo'])
                ->columnSpanFull(),
        ];
    }

    public function save(): void
    {
        FooterSetting::current()->update($this->form->getState());

        Notification::make()
            ->success()
            ->title('Footer disimpan')
            ->send();
    }

    /**
     * Publik, bukan protected: berkas Blade merender di luar lingkup kelas ini,
     * sehingga metode protected tidak dapat dipanggil dari sana.
     *
     * @return array<Action>
     */
    public function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan')
                ->submit('save'),
        ];
    }
}
