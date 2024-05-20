<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'logo', 'licence_number', 'description', 'instructions'
    ];

    // Accessor for the logo URL
    public function getLogoUrlAttribute()
    {
        return $this->logo ? Storage::url($this->logo) : null;
    }
    public static function getFirstLogoUrl()
    {
        $application = self::first();
        return $application ? $application->logo_url : null;
    }
    public static function getFirstName()
    {
        $application = self::first();
        return $application ? $application->name : null;
    }

    public static function getFirstLicense()
    {
        $application = self::first();
        return $application ? $application->licence_number : null;
    }
}
