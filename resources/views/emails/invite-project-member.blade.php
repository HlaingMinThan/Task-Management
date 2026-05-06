<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
    <h2 style="color: #333;">You're invited to collaborate!</h2>

    <p style="color: #666; font-size: 16px;">
        Hi there,
    </p>

    <p style="color: #666; font-size: 16px;">
        <strong>{{ $inviter->name }}</strong> has invited you to join the project
        <strong>{{ $project->name }}</strong> as a <strong>{{ $invite->role }}</strong>.
    </p>

    <p style="color: #666; font-size: 16px;">
        Click the button below to accept the invitation:
    </p>

    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ $acceptUrl }}" style="display: inline-block; padding: 12px 30px; background-color: #3b82f6; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">
            Accept Invitation
        </a>
    </div>

    <p style="color: #999; font-size: 14px;">
        This invitation will expire in 24 hours.
    </p>

    <p style="color: #999; font-size: 14px;">
        If you didn't expect this invitation or have any questions, please contact <strong>{{ $inviter->name }}</strong> directly.
    </p>

    <hr style="border: none; border-top: 1px solid #eee; margin: 30px 0;">

    <p style="color: #999; font-size: 12px; text-align: center;">
        © {{ date('Y') }} Task Management. All rights reserved.
    </p>
</div>
