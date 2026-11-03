@extends('layouts.app')

@section('title', $title)
@section('description', $subtitle)

@php
    $credits = [
        // Package pages (chimpanzee, north-east, coast and island trips).
        ['subject' => 'Chimpanzees in Gombe (chimpanzee safari)', 'author' => 'Cethuyghe', 'license' => 'CC BY-SA 4.0', 'href' => 'https://commons.wikimedia.org/wiki/File:Chimps_in_Gombe.jpg'],
        ['subject' => 'Usambara Mountains (Mkomazi, Usambara & Saadani trip)', 'author' => 'Joachim Huber', 'license' => 'CC BY-SA 2.0', 'href' => 'https://commons.wikimedia.org/wiki/File:Usambara_Mountains,_Tanzania_(2392821517).jpg'],
        ['subject' => 'Baobab in Saadani National Park', 'author' => 'Rasheedhrasheed', 'license' => 'CC BY-SA 4.0', 'href' => 'https://commons.wikimedia.org/wiki/File:A_baobab_Tree_in_Saadani_National_Park.jpg'],
        ['subject' => 'Mkomazi National Park', 'author' => 'Nicola Avery', 'license' => 'CC BY-SA 2.0', 'href' => 'https://commons.wikimedia.org/wiki/File:Mkomazi_National_Park-3.jpg'],
        ['subject' => 'Vumawimbi Beach, Pemba Island', 'author' => 'Marcel Oosterwijk', 'license' => 'CC BY-SA 2.0', 'href' => 'https://commons.wikimedia.org/wiki/File:Vumawimbi_Beach,_Pemba_Island.jpg'],
        // Homepage "Top activities".
        ['subject' => 'Walking safari, Katavi National Park (homepage activities)', 'author' => 'Brian Harries', 'license' => 'CC BY 2.0', 'href' => 'https://commons.wikimedia.org/wiki/File:Walking_Safari_in_Katavi_National_Park.jpg'],
        ['subject' => 'Hippos in the Rufiji River (homepage activities)', 'author' => 'Richard Mortel', 'license' => 'CC BY 2.0', 'href' => 'https://commons.wikimedia.org/wiki/File:Hippo_in_the_Rufiji_River,_Selous_Game_Reserve_(4)_(28955071452).jpg'],
        ['subject' => 'Maasai dancers, Ngorongoro Conservation Area (homepage activities)', 'author' => 'Rwebogora', 'license' => 'CC BY-SA 4.0', 'href' => 'https://commons.wikimedia.org/wiki/File:Maasai_dancers_in_the_Ngorongoro_Conservation_Area,_Tanzania_01.jpg'],
        ['subject' => 'Coffee cherries, Karatu (homepage activities)', 'author' => 'Nina R', 'license' => 'CC BY 2.0', 'href' => 'https://commons.wikimedia.org/wiki/File:Karatu,_Tanzania_(51490064825).jpg'],
        ['subject' => 'Snorkelling trip (homepage activities)', 'author' => 'Mike from Vancouver, Canada', 'license' => 'CC BY-SA 2.0', 'href' => 'https://commons.wikimedia.org/wiki/File:Snorkeling_3_(14703965432).jpg'],
        // Destinations pages (/destinations).
        ['subject' => 'Elephants and baobab, Tarangire (Destinations)', 'author' => 'Leyo', 'license' => 'CC BY-SA 2.5 CH', 'href' => 'https://commons.wikimedia.org/wiki/File:Elefanten_fressen_Baobab-Rinde.jpg'],
        ['subject' => 'Wildlife at Lake Manyara (Destinations)', 'author' => 'Gaurav Pandit', 'license' => 'CC BY-SA 3.0', 'href' => 'https://commons.wikimedia.org/wiki/File:Lake_Manyara_Wildlife.jpg'],
        ['subject' => 'Ngurdoto Crater, Arusha National Park (Destinations)', 'author' => 'ProfessorX', 'license' => 'Public domain', 'href' => 'https://commons.wikimedia.org/wiki/File:Arusha-NatPark-Ngurdoto-Crater.jpg'],
        ['subject' => 'Rufiji River, Nyerere / Selous (Destinations)', 'author' => 'BBM Explorer', 'license' => 'CC BY 2.0', 'href' => 'https://commons.wikimedia.org/wiki/File:Rufiji_River,_Selous_Game_Reserve.jpg'],
        ['subject' => 'Ol Doinyo Lengai from Lake Natron (Destinations)', 'author' => 'Clem23', 'license' => 'CC BY-SA 3.0', 'href' => 'https://commons.wikimedia.org/wiki/File:Lengai_from_Natron.jpg'],
        ['subject' => 'Giraffes in Mikumi National Park (Destinations)', 'author' => 'Rasheedhrasheed', 'license' => 'CC BY-SA 4.0', 'href' => 'https://commons.wikimedia.org/wiki/File:Giraffes_in_Mikumi_National_Park,_Tanzania.jpg'],
        ['subject' => 'Mafia Island beach (Destinations)', 'author' => 'Gill Penney', 'license' => 'CC BY 2.0', 'href' => 'https://commons.wikimedia.org/wiki/File:Mafia_Island,_Tanzania.jpg'],
        // Our Vehicles page (/vehicles). "Modified" = another company's logo removed or photo cropped.
        ['subject' => 'Toyota Land Cruiser 4×4, Tarangire (Our Vehicles; modified)', 'author' => 'xorge', 'license' => 'CC BY-SA 2.0', 'href' => 'https://commons.wikimedia.org/wiki/File:Tarangine_(1)_(14145531012).jpg'],
        ['subject' => 'Open-sided game viewer (Our Vehicles)', 'author' => 'DDUGGAN12', 'license' => 'CC BY-SA 4.0', 'href' => 'https://commons.wikimedia.org/wiki/File:Lion_Safari.jpg'],
        ['subject' => 'Land Rover Defender, Serengeti (Our Vehicles)', 'author' => 'Daniel Rosengren', 'license' => 'CC BY 4.0', 'href' => 'https://commons.wikimedia.org/wiki/File:Landrover_and_plane_in_Serengeti,_Tanzania..jpg'],
        ['subject' => 'Safari minivan, Ngorongoro (Our Vehicles; cropped)', 'author' => 'Muhammad Mahdi Karim', 'license' => 'GFDL 1.2', 'href' => 'https://commons.wikimedia.org/wiki/File:Safari_in_Ngorongoro.jpg'],
        ['subject' => 'Toyota Land Cruiser Prado (Our Vehicles)', 'author' => 'OSX', 'license' => 'Public domain', 'href' => 'https://commons.wikimedia.org/wiki/File:2009-2011_Toyota_Land_Cruiser_Prado_(KDJ150R)_VX_5-door_wagon_(2011-10-25)_02.jpg'],
        ['subject' => 'Toyota Land Cruiser V8 (Our Vehicles)', 'author' => 'OSX', 'license' => 'Public domain', 'href' => 'https://commons.wikimedia.org/wiki/File:2013_Toyota_Land_Cruiser_(VDJ200R_MY13)_Sahara_wagon_(2015-07-24)_01.jpg'],
        ['subject' => 'Toyota HiAce (Our Vehicles)', 'author' => 'Manoj Prasad', 'license' => 'CC BY-SA 2.0', 'href' => 'https://commons.wikimedia.org/wiki/File:2014_Toyota_HiAce_Window_Van_(19110194534).jpg'],
        ['subject' => 'Toyota Coaster (Our Vehicles; modified)', 'author' => 'Kk70088', 'license' => 'CC BY-SA 4.0', 'href' => 'https://commons.wikimedia.org/wiki/File:B137150.jpg'],
        ['subject' => 'Mount Kilimanjaro (featured packages, Machame Route page & Destinations)', 'author' => 'Sergey Pesterev', 'license' => 'CC BY-SA 4.0', 'href' => 'https://commons.wikimedia.org/wiki/File:Kilimanjaro_from_Amboseli.jpg'],
        ['subject' => 'Ngorongoro Crater (Northern Circuit menu, featured packages, Ngorongoro safari page & Destinations)', 'author' => 'Grahampurse', 'license' => 'CC BY-SA 4.0', 'href' => 'https://commons.wikimedia.org/wiki/File:Ngorongoro-1001-2.jpg'],
        ['subject' => 'Zanzibar beach, Nungwi (featured packages, Zanzibar page & Destinations)', 'author' => 'Sansibar-freundeskreis.de', 'license' => 'CC BY-SA 4.0', 'href' => 'https://commons.wikimedia.org/wiki/File:Original_4b713299-08fb-4d32-9299-6844e8a97a78_IMG_20211201_154556.jpg'],
        ['subject' => 'Lion, Serengeti (Safaris menu, page, Ngorongoro safari page & Destinations)', 'author' => 'Giles Laurent', 'license' => 'CC BY-SA 4.0', 'href' => 'https://commons.wikimedia.org/wiki/File:020_The_lion_king_Snyggve_in_the_Serengeti_National_Park_Photo_by_Giles_Laurent.jpg'],
        ['subject' => 'Elephants, Ruaha (Southern Circuit menu & Destinations)', 'author' => 'Brian Harries', 'license' => 'CC BY 2.0', 'href' => 'https://commons.wikimedia.org/wiki/File:Purple_On_The_River.jpg'],
        ['subject' => 'Mawenzi peak (Kilimanjaro menu & Machame Route page)', 'author' => 'ProfessorX', 'license' => 'Public domain', 'href' => 'https://commons.wikimedia.org/wiki/File:Mawenzi-from-Kibohut_800600.jpg'],
        ['subject' => "Sultan's Palace, Stone Town (Zanzibar menu & page)", 'author' => 'Mbz1', 'license' => 'CC BY-SA 3.0', 'href' => 'https://commons.wikimedia.org/wiki/File:Zanzibar_sultan_palace.jpg'],
        ['subject' => 'Chili peppers, spice tour (Zanzibar page)', 'author' => 'Mike from Vancouver, Canada', 'license' => 'CC BY-SA 2.0', 'href' => 'https://commons.wikimedia.org/wiki/File:Spice_tour_2_(14701047191).jpg'],
        ['subject' => 'Dhow sailing boat (Zanzibar page)', 'author' => 'Muhammad Mahdi Karim', 'license' => 'GFDL 1.2', 'href' => 'https://commons.wikimedia.org/wiki/File:Dhow_znz.jpg'],
        ['subject' => 'Materuni Falls (Day Trips menu)', 'author' => 'Daniel Msirikale', 'license' => 'CC BY-SA 4.0', 'href' => 'https://commons.wikimedia.org/wiki/File:Materuni_Falls_1.jpg'],
        ['subject' => 'Arusha city view (About menu)', 'author' => 'Halidtz', 'license' => 'CC BY-SA 4.0', 'href' => 'https://commons.wikimedia.org/wiki/File:Arusha_City_view.jpg'],
    ];
@endphp

@section('content')
<section class="page-banner">
    <div class="page-banner__inner">
        <p class="page-banner__eyebrow">Serengeti Roaming African Safaris</p>
        <h1 class="page-banner__title">{{ $title }}</h1>
        <p class="page-banner__text">{{ $subtitle }}</p>
    </div>
</section>

<section class="mx-auto max-w-3xl px-4 py-16 lg:px-8">
    <p class="text-sm leading-relaxed text-charcoal-600">
        Until our own photography is ready, this site uses the images below from Wikimedia Commons.
        Each is credited here per its Creative Commons or public-domain licence.
    </p>

    <ul class="mt-8 divide-y divide-forest-100 rounded-2xl border border-forest-100 bg-white">
        @foreach ($credits as $credit)
            <li class="flex flex-wrap items-center justify-between gap-2 px-5 py-4 text-sm">
                <span class="text-charcoal-900">{{ $credit['subject'] }}</span>
                <span class="text-charcoal-500">
                    {{ $credit['author'] }} ·
                    <a href="{{ $credit['href'] }}" target="_blank" rel="noopener nofollow" class="text-gold-600 hover:underline">
                        {{ $credit['license'] }}
                    </a>
                </span>
            </li>
        @endforeach
    </ul>
</section>
@endsection
