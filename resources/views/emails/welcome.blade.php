<x-mail::message>
# Welcome to Hijrah App! 🎉

Hello {{ $user->name ?? $user->email }},

Welcome to Hijrah App! We're excited to have you join our community.

Your account has been successfully verified and activated. You can now access all the features of our app.

## What's Next?

- Complete your profile
- Explore our features
- Connect with other users
- Start your journey with us

If you have any questions or need assistance, feel free to reach out to our support team.

Thanks for choosing Hijrah App!<br>
{{ config('app.name') }}
</x-mail::message> 