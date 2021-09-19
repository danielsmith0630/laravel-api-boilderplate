<?php

namespace App\Services;

use App\Models\User;
use App\Models\LinkedSocialAccount;
use Laravel\Socialite\Two\User as ProviderUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SocialAccountsService
{
    /**
     * Find or create user instance by provider user instance and provider name.
     * 
     * @param ProviderUser $providerUser
     * @param string $provider
     * 
     * @return User
     */
    public function findOrCreate(ProviderUser $providerUser, string $provider): User
    {
        $linkedSocialAccount = LinkedSocialAccount::where('provider_name', $provider)
            ->where('provider_id', $providerUser->getId())
            ->first();

        if ($linkedSocialAccount) {
            return $linkedSocialAccount->user;
        } else {
            $user = null;
            $email = $providerUser->getEmail();

            if ($email) {
                $user = User::where('email', $email)->first();
            } else {
                return null;
            }

            if (!$user) {
                $user = User::create([
                    'email' => $email,
                    'password' => Hash::make(Str::random(16)),
                ]);

                $name = $providerUser->getName();
                $names = explode(' ', $name, 2);

                $firstName = $names[0];
                $lastName = '';
                if (count($names) > 1) {
                    $lastName = $names[1];
                }

                $user->profile()->create([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                ]);
            }

            $user->linkedSocialAccounts()->create([
                'provider_id' => $providerUser->getId(),
                'provider_name' => $provider,
            ]);

            return $user;
        }
    }
}