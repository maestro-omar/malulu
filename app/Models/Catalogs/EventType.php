<?php

namespace App\Models\Catalogs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Entities\RecurrentEvent;
use App\Models\Entities\AcademicEvent;
use Illuminate\Support\Collection;

class EventType extends Model
{
    use HasFactory;

    const SCOPE_NACIONAL = 'nacional';
    const SCOPE_PROVINCIAL = 'provincial';
    const SCOPE_ESCOLAR = 'escolar';

    const CODE_FERIADO_NACIONAL = 'feriado_nacional';
    const CODE_CONMEMORACION_NACIONAL = 'conmemoracion_nacional';
    const CODE_SUSPENCION_NACIONAL = 'suspencion_nacional';

    const CODE_FERIADO_PROVINCIAL = 'feriado_provincial';
    const CODE_CONMEMORACION_PROVINCIAL = 'conmemoracion_provincial'; //
    const CODE_SUSPENCION_PROVINCIAL = 'suspencion_provincial'; //bajas temperaturas
    const CODE_ACADEMICO_PROVINCIAL = 'academico_provincial'; //feria de ciencias

    const CODE_CONMEMORACION_ESCOLAR = 'conmemoracion_escolar';
    const CODE_SUSPENCION_ESCOLAR = 'suspencion_escolar';
    const CODE_INICIO_CLASES = 'inicio_clases';
    const CODE_FIN_CLASES = 'fin_clases';
    const CODE_INICIO_INVIERNO = 'inicio_invierno';
    const CODE_FIN_INVIERNO = 'fin_invierno';
    const CODE_ENTREGA_LIBRETA = 'entrega_libreta';
    const CODE_SALIDA_DIDACTICA = 'salida_didactica';
    const CODE_REUNION_DOCENTE = 'reunion_docente';
    const CODE_REUNION_GRUPAL = 'reunion_grupal';
    const CODE_EXAMEN = 'examen';
    const CODE_ACADEMICO_ESCOLAR = 'academico_escolar';

    protected $table = 'event_types';

    protected $fillable = [
        'code',
        'name',
        'scope',
    ];

    // Scopes
    public function scopeNational($query)
    {
        return $query->where('scope', 'national');
    }

    public function scopeProvincial($query)
    {
        return $query->where('scope', 'provincial');
    }

    public function scopeSchool($query)
    {
        return $query->where('scope', 'school');
    }

    public function recurrentEvents()
    {
        return $this->hasMany(RecurrentEvent::class, 'event_type_id');
    }

    public function academicEvents()
    {
        return $this->hasMany(AcademicEvent::class, 'event_type_id');
    }

    public static function forDropdown(): Collection
    {
        return self::orderBy('scope')->orderBy('name')->get()->map(function (EventType $eventType) {
            return [
                'id' => $eventType->id,
                'label' => $eventType->name . ' (' . $eventType->labelForScope($eventType->scope) . ')',
                'code' => $eventType->code,
            ];
        })->values();
    }

    public static function labelForScope($code): ?string
    {
        return match ($code) {
            self::SCOPE_NACIONAL => 'Nacional',
            self::SCOPE_PROVINCIAL => 'Provincial',
            self::SCOPE_ESCOLAR => 'Escolar',
            default => null,
        };
    }
}
