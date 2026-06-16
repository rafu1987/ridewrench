<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

final class LanguageController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $availableLocales = array_keys(config('ridewrench.languages', []));

        $validated = $request->validate([
            'language' => ['required', 'string', 'in:' . implode(',', $availableLocales)],
            'redirect' => ['nullable', 'string'],
        ]);

        $locale = $validated['language'];

        $request->session()->put('locale', $locale);
        App::setLocale($locale);

        if ($request->user()) {
            DB::table('users')
                ->where('id', $request->user()->id)
                ->update([
                    'language' => $locale,
                    'updated_at' => now(),
                ]);
        }

        $redirect = (string) ($validated['redirect'] ?? '/');
        $redirectPath = parse_url($redirect, PHP_URL_PATH) ?: '/';

        if (!str_starts_with($redirectPath, '/')) {
            $redirectPath = '/';
        }

        $query = parse_url($redirect, PHP_URL_QUERY);

        if ($query) {
            $redirectPath .= '?' . $query;
        }

        return redirect($redirectPath);
    }
}
