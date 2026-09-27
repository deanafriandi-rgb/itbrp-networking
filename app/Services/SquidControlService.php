<?php

namespace App\Services;

use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

class SquidControlService
{
    public function __construct(
        private SquidEnvironmentGuardService $guard
    ) {
    }


    /**
     * Mode Squid.
     */
    public function getMode(): string
    {
        return strtolower(
            (string) config(
                'squid.mode',
                'local'
            )
        );
    }


    /**
     * Binary Squid.
     */
    public function getBinary(): string
    {
        $binary = trim(
            (string) config(
                'squid.commands.binary',
                'squid'
            )
        );


        if ($binary === '') {

            throw new RuntimeException(
                'Binary Squid belum dikonfigurasi.'
            );
        }


        return $binary;
    }


    /**
     * Status izin reconfigure.
     */
    public function isReconfigureEnabled(): bool
    {
        return (bool) config(
            'squid.reconfigure_enabled',
            false
        );
    }


    /**
     * Informasi Squid Control.
     */
    public function status(): array
    {
        return [
            'mode' =>
                $this->getMode(),

            'binary' =>
                $this->getBinary(),

            'reconfigure_enabled' =>
                $this->isReconfigureEnabled(),

            'host' =>
                config('squid.host'),

            'ports' => [

                'forward' =>
                    config(
                        'squid.ports.forward'
                    ),

                'http_intercept' =>
                    config(
                        'squid.ports.http_intercept'
                    ),

                'https_intercept' =>
                    config(
                        'squid.ports.https_intercept'
                    ),
            ],

            'blacklist_file' =>
                config(
                    'squid.blacklist_file'
                ),

            'access_log' =>
                config(
                    'squid.access_log'
                ),

            /*
            |--------------------------------------------------------------------------
            | Safety Information
            |--------------------------------------------------------------------------
            */

            'safety' =>
                $this->guard->inspect(),
        ];
    }


    /**
     * Validasi konfigurasi Squid.
     */
    public function parseConfig(): array
    {
        /*
        |--------------------------------------------------------------------------
        | LOCAL
        |--------------------------------------------------------------------------
        */

        if (
            $this->getMode()
            === 'local'
        ) {

            return [
                'success' => true,

                'executed' => false,

                'skipped' => true,

                'safety_blocked' =>
                    false,

                'action' =>
                    'parse',

                'mode' =>
                    'local',

                'command' =>
                    'squid -k parse',

                'exit_code' =>
                    null,

                'output' =>
                    '',

                'error_output' =>
                    '',

                'message' =>
                    'Validasi Squid dilewati karena masih mode local.',
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | PRODUCTION SAFETY
        |--------------------------------------------------------------------------
        */

        $safety =
            $this->guard->inspect();


        if (
            !(
                $safety[
                    'can_execute'
                ]
                ?? false
            )
        ) {

            return $this->safetyBlockedResult(
                'parse',
                $safety
            );
        }


        /*
        |--------------------------------------------------------------------------
        | EXECUTE
        |--------------------------------------------------------------------------
        */

        return $this->runSquidCommand(
            [
                $this->getBinary(),
                '-k',
                'parse',
            ],
            'parse'
        );
    }


    /**
     * Reload konfigurasi Squid.
     *
     * Selalu parse terlebih dahulu.
     */
    public function reconfigure(): array
    {
        /*
        |--------------------------------------------------------------------------
        | LOCAL
        |--------------------------------------------------------------------------
        */

        if (
            $this->getMode()
            === 'local'
        ) {

            return [
                'success' => true,

                'executed' => false,

                'skipped' => true,

                'safety_blocked' =>
                    false,

                'action' =>
                    'reconfigure',

                'mode' =>
                    'local',

                'command' =>
                    'squid -k reconfigure',

                'exit_code' =>
                    null,

                'output' =>
                    '',

                'error_output' =>
                    '',

                'message' =>
                    'Reconfigure Squid dilewati karena masih mode local.',
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | PRODUCTION SAFETY
        |--------------------------------------------------------------------------
        */

        $safety =
            $this->guard->inspect();


        if (
            !(
                $safety[
                    'can_execute'
                ]
                ?? false
            )
        ) {

            return $this->safetyBlockedResult(
                'reconfigure',
                $safety
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Reconfigure Switch
        |--------------------------------------------------------------------------
        */

        if (
            !$this->isReconfigureEnabled()
        ) {

            return [
                'success' =>
                    false,

                'executed' =>
                    false,

                'skipped' =>
                    true,

                'safety_blocked' =>
                    false,

                'action' =>
                    'reconfigure',

                'mode' =>
                    $this->getMode(),

                'command' =>
                    $this->getBinary()
                    .
                    ' -k reconfigure',

                'exit_code' =>
                    null,

                'output' =>
                    '',

                'error_output' =>
                    '',

                'message' =>
                    'Reconfigure Squid dinonaktifkan melalui SQUID_RECONFIGURE_ENABLED.',
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | Parse First
        |--------------------------------------------------------------------------
        */

        $parse =
            $this->parseConfig();


        if (
            !(
                $parse[
                    'success'
                ]
                ?? false
            )
        ) {

            return [
                'success' =>
                    false,

                'executed' =>
                    false,

                'skipped' =>
                    true,

                'safety_blocked' =>
                    $parse[
                        'safety_blocked'
                    ]
                    ?? false,

                'action' =>
                    'reconfigure',

                'mode' =>
                    $this->getMode(),

                'command' =>
                    $this->getBinary()
                    .
                    ' -k reconfigure',

                'exit_code' =>
                    null,

                'output' =>
                    '',

                'error_output' =>
                    $parse[
                        'error_output'
                    ]
                    ?? '',

                'message' =>
                    'Reconfigure dibatalkan karena validasi Squid gagal.',

                'parse_result' =>
                    $parse,
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | Reconfigure
        |--------------------------------------------------------------------------
        */

        $result =
            $this->runSquidCommand(
                [
                    $this->getBinary(),
                    '-k',
                    'reconfigure',
                ],
                'reconfigure'
            );


        $result['parse_result'] =
            $parse;


        return $result;
    }


    /**
     * Parse kemudian reconfigure.
     */
    public function apply(): array
    {
        return $this->reconfigure();
    }


    /**
     * Response jika safety guard memblokir command.
     */
    private function safetyBlockedResult(
        string $action,
        array $safety
    ): array {

        $issues =
            $safety['issues']
            ?? [];


        $error =
            !empty($issues)

                ? implode(
                    ' ',
                    $issues
                )

                : (
                    $safety['message']
                    ??
                    'Environment Squid tidak aman.'
                );


        return [
            'success' =>
                false,

            'executed' =>
                false,

            'skipped' =>
                true,

            'safety_blocked' =>
                true,

            'action' =>
                $action,

            'mode' =>
                $this->getMode(),

            'command' =>
                $this->getBinary()
                .
                ' -k '
                .
                $action,

            'exit_code' =>
                null,

            'output' =>
                '',

            'error_output' =>
                $error,

            'message' =>
                'Command Squid diblokir oleh production safety guard.',

            'safety' =>
                $safety,
        ];
    }


    /**
     * Jalankan Squid tanpa shell string.
     */
    private function runSquidCommand(
        array $command,
        string $action
    ): array {
        try {

            $process =
                new Process(
                    $command
                );


            $process->setTimeout(
                30
            );


            $process->run();


            $success =
                $process->isSuccessful();


            return [
                'success' =>
                    $success,

                'executed' =>
                    true,

                'skipped' =>
                    false,

                'safety_blocked' =>
                    false,

                'action' =>
                    $action,

                'mode' =>
                    $this->getMode(),

                'command' =>
                    implode(
                        ' ',
                        $command
                    ),

                'exit_code' =>
                    $process
                        ->getExitCode(),

                'output' =>
                    trim(
                        $process
                            ->getOutput()
                    ),

                'error_output' =>
                    trim(
                        $process
                            ->getErrorOutput()
                    ),

                'message' =>
                    $success

                        ? "Squid {$action} berhasil."

                        : "Squid {$action} gagal.",
            ];

        } catch (Throwable $e) {

            return [
                'success' =>
                    false,

                'executed' =>
                    false,

                'skipped' =>
                    false,

                'safety_blocked' =>
                    false,

                'action' =>
                    $action,

                'mode' =>
                    $this->getMode(),

                'command' =>
                    implode(
                        ' ',
                        $command
                    ),

                'exit_code' =>
                    null,

                'output' =>
                    '',

                'error_output' =>
                    $e->getMessage(),

                'message' =>
                    "Gagal menjalankan Squid {$action}.",
            ];
        }
    }
}