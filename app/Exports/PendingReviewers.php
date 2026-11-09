<?php

namespace App\Exports;

use App\Models\Admin;
use App\Models\InviteReviewer;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Font;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PendingReviewersExport
{
    public function download(string $filename = 'pending-reviewers.xlsx'): StreamedResponse
    {
        $registeredEmails = Admin::pluck('email')
            ->map(fn($e) => strtolower($e))
            ->toArray();

        $records = InviteReviewer::whereNotIn('email', $registeredEmails)->get();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        // Header row
        $headers = ['Email', 'Invitations Sent', 'Last Invited At', 'First Invited At'];
        foreach ($headers as $col => $heading) {
            $cell = $sheet->getCellByColumnAndRow($col + 1, 1);
            $cell->setValue($heading);
            $cell->getStyle()->getFont()->setBold(true);
        }

        // Data rows
        foreach ($records as $index => $record) {
            $row = $index + 2;
            $sheet->getCellByColumnAndRow(1, $row)->setValue($record->email);
            $sheet->getCellByColumnAndRow(2, $row)->setValue($record->invitation_count);
            $sheet->getCellByColumnAndRow(3, $row)->setValue(
                $record->last_invited_at?->format('d-M-Y H:i') ?? '—'
            );
            $sheet->getCellByColumnAndRow(4, $row)->setValue(
                $record->created_at->format('d-M-Y H:i')
            );
        }

        // Auto-size columns
        foreach (range(1, 4) as $col) {
            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);

        return new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'max-age=0',
        ]);
    }
}
