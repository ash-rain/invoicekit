<?php

namespace App\Http\Controllers;

use App\Models\InvoiceAccessToken;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class InvoicePortalController extends Controller
{
    public function show(Request $request, string $token)
    {
        $accessToken = InvoiceAccessToken::where('token', $token)
            ->with(['invoice.items', 'invoice.client', 'invoice.user.currentCompany'])
            ->firstOrFail();

        if ($accessToken->isExpired()) {
            abort(410, 'This portal link has expired.');
        }

        if ($accessToken->isPasswordProtected()) {
            $sessionKey = 'portal_auth_'.$token;

            if (! $request->session()->get($sessionKey)) {
                return view('invoices.portal-auth', compact('accessToken'));
            }
        }

        $accessToken->update(['accessed_at' => now()]);

        $invoice = $accessToken->invoice;
        $company = $invoice->user->currentCompany;

        if ($request->boolean('download')) {
            $lang = $invoice->language ?? 'en';
            $previousLocale = app()->getLocale();
            app()->setLocale($lang);
            $templateService = app(\App\Services\InvoiceTemplateService::class);
            $view = $templateService->getTemplatePath($templateService->resolveForInvoice($invoice, $company));
            $pdf = Pdf::loadView($view, compact('invoice', 'lang', 'company'));
            app()->setLocale($previousLocale);

            return $pdf->stream("invoice-{$invoice->invoice_number}.pdf");
        }

        return view('invoices.portal', compact('invoice', 'company', 'accessToken'));
    }

    public function authenticate(Request $request, string $token)
    {
        $accessToken = InvoiceAccessToken::where('token', $token)->firstOrFail();

        if ($accessToken->isExpired()) {
            abort(410, 'This portal link has expired.');
        }

        $request->validate(['password' => ['required', 'string']]);

        if (! Hash::check($request->password, $accessToken->password_hash)) {
            return back()->withErrors(['password' => 'Incorrect password.']);
        }

        $request->session()->put('portal_auth_'.$token, true);

        return redirect()->route('invoice.portal', $token);
    }
}
