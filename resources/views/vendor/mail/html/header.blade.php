@props(['url', 'message' => null])
<tr>
<td class="header" style="text-align: center; padding: 25px 0 20px 0;">
<a href="{{ $url }}" style="display: inline-block; width: 100%; max-width: 530px; text-decoration: none; background-color: #0a3957; padding: 18px 24px; border-radius: 12px; box-shadow: 0 4px 10px rgba(10, 57, 87, 0.25); text-align: center;">
    <img src="{{ isset($message) ? $message->embed(public_path('Images/logoUASLP.png')) : asset('Images/logoUASLP.png') }}" alt="UASLP" style="height: 48px; width: auto; vertical-align: middle; margin-right: 25px; border: none; display: inline-block;">
    <img src="{{ isset($message) ? $message->embed(public_path('Images/logo-facultad-ingenieria.jpg')) : asset('Images/logo-facultad-ingenieria.jpg') }}" alt="Facultad de Ingeniería UASLP" style="height: 68px; width: auto; vertical-align: middle; border: none; display: inline-block;">
</a>
</td>
</tr>
