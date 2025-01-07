<?php

echo $_SERVER['REQUEST_URI'];

$config = json_decode(file_get_contents(__DIR__ . '/../config.json'), true);

$guilds = [];
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://discord.com/api/v9/users/@me/guilds');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: ' . $config['token']
]);
$response = curl_exec($ch);
$guilds = json_decode($response, true);
curl_close($ch);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://discord.com/api/v9/users/@me');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: ' . $config['token']
]);
$response = curl_exec($ch);
$user = json_decode($response, true);
curl_close($ch);

// Extract guild ID from URL
$guildId = null;
if (preg_match('/\/channels\/(\d+)/', $_SERVER['REQUEST_URI'], $matches)) {
    $guildId = $matches[1];
}

// Fetch guild data and channels if guild ID is present
$guildData = null;
$channels = [];
if ($guildId) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://discord.com/api/v9/guilds/' . $guildId);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: ' . $config['token']
    ]);
    $response = curl_exec($ch);
    $guildData = json_decode($response, true);
    curl_close($ch);

    // Fetch channels
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://discord.com/api/v9/guilds/' . $guildId . '/channels');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: ' . $config['token']
    ]);
    $response = curl_exec($ch);
    $channels = json_decode($response, true);
    curl_close($ch);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $config['app_name'] ?></title>
    <link rel="icon" href="/img/icon.png">

    <link rel="stylesheet" href="/styles/general.css">
    <link rel="stylesheet" href="/styles/header.css">
    <link rel="stylesheet" href="/styles/server-icon-list.css">
    <link rel="stylesheet" href="/styles/chat-list.css">
    <link rel="stylesheet" href="/styles/message-log.css">
    <link rel="stylesheet" href="/styles/member-list.css">

    <script src="https://kit.fontawesome.com/7fece9eebc.js" crossorigin="anonymous"></script>
</head>

<body>
    <div class="content">
        <ul class="server-icon-list">
            <li class="server-icon-button">
                <img class="discord-icon" src="/img/discord-icon.png" alt="Discord Icon">
            </li>

            <div class="line"></div>

            <?php foreach ($guilds as $guild) : ?>
                <li class="server-icon-button" title="<?php echo $guild['name']; ?>">
                    <a href="/channels/<?php echo $guild['id']; ?>">
                        <img class="server-icon" src="<?php echo $guild['icon'] ? 'https://cdn.discordapp.com/icons/' . $guild['id'] . '/' . $guild['icon'] . '.png' : '/img/default-icon.png'; ?>" alt="<?php echo $guild['name']; ?>">
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="header-container">
            <div class="header-1">
                <p class="group-name"><?php echo $guildData ? $guildData['name'] : 'Winner Group'; ?></p>
                <i class="fa-solid fa-chevron-down"></i>
            </div>

            <div class="header-2">
                <div class="header-channel-container">
                    <i class="fa-solid fa-hashtag"></i>
                    <p class="channel-name">Gaming</p>
                </div>
                <img class="thread-icon" src="/img/thread.png" alt="Thread Icon">
                <i class="fa-solid fa-bell fa-lg"></i>
                <i class="fa-solid fa-thumbtack fa-lg"></i>
                <i class="fa-solid fa-users fa-lg"></i>
                <div class="search-bar-container">
                    <input class="search-bar" type="text" placeholder="Search">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                </div>
                <i class="fa-solid fa-inbox fa-lg"></i>
                <i class="fa-solid fa-circle-question fa-lg"></i>
            </div>
        </div>

        <div class="chat-list">
            <div class="event-tab">
                <i class="fa-regular fa-calendar event-icon fa-lg"></i>
                <p class="event-text">Events</p>
            </div>

            <div class="chat-list-line-1"></div>

            <div class="text-channel-tab">
                <?php
                $categories = [];
                foreach ($channels as $channel) {
                    if ($channel['type'] == 4) { // Category
                        $categories[$channel['id']] = [
                            'name' => $channel['name'],
                            'channels' => []
                        ];
                    }
                }

                foreach ($channels as $channel) {
                    if ($channel['type'] != 4 && isset($categories[$channel['parent_id']])) {
                        $categories[$channel['parent_id']]['channels'][] = $channel;
                    }
                }
                ?>

                <?php foreach ($channels as $channel) : ?>
                    <?php if ($channel['type'] != 4 && !isset($categories[$channel['parent_id']])) : ?>
                        <?php if ($channel['type'] == 2) : // Voice channel 
                        ?>
                            <li class="channel-button">
                                <i class="fa-solid fa-volume-high"></i>
                                <p class="channel-name"><?php echo $channel['name']; ?></p>
                            </li>
                        <?php else: ?>
                            <li class="channel-button">
                                <i class="fa-solid fa-hashtag"></i>
                                <p class="channel-name"><?php echo $channel['name']; ?></p>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endforeach; ?>

                <?php foreach ($categories as $category) : ?>
                    <div class="text-channel-tab">
                        <div class="channel-container">
                            <i class="fa-solid fa-chevron-down fa-2xs"></i>
                            <p class="channel-category-name"><?php echo $category['name']; ?></p>
                            <i class="fa-solid fa-plus fa-xs"></i>
                        </div>
                        <ul class="channel-list">
                            <?php foreach ($category['channels'] as $channel) : ?>
                                <?php if ($channel['type'] == 2) : // Voice channel 
                                ?>
                                    <li class="channel-button">
                                        <i class="fa-solid fa-volume-high"></i>
                                        <p class="channel-name"><?php echo $channel['name']; ?></p>
                                    </li>
                                <?php else: ?>
                                    <li class="channel-button">
                                        <i class="fa-solid fa-hashtag"></i>
                                        <p class="channel-name"><?php echo $channel['name']; ?></p>
                                    </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>

                <div class="profile-bar">
                    <div class="profile-picture-container">
                        <img class="profile-picture" src="<?php echo $user['avatar'] ? 'https://cdn.discordapp.com/avatars/' . $user['id'] . '/' . $user['avatar'] . '.png' : '/img/default-avatar.png'; ?>" alt="Profile Picture">
                        <div class="status-holder">
                            <i class="fa-solid fa-circle status-online"></i>
                        </div>
                    </div>
                    <div class="member-text">
                        <p class="profile-member-name"><?php echo $user['username']; ?></p>
                        <p class="member-status"><?php echo $user['status'] ?? 'Online'; ?></p>
                    </div>
                    <div class="profile-icons">
                        <i class="fa-solid fa-microphone fa-lg"></i>
                        <i class="fa-solid fa-headphones fa-lg"></i>
                        <i class="fa-solid fa-gear fa-lg"></i>
                    </div>
                </div>
            </div>

            <div class="message-log">
                <div class="message-header">
                    <div class="message-header-container">
                        <img class="message-header-image" src="/img/hashtag.png">
                    </div>
                    <p class="message-header-1">
                        Welcome to #Gaming!
                    </p>
                    <p class="message-header-2">
                        This is the start of the #Gaming channel.
                    </p>
                    <button class="edit-channel-button">
                        <i class="fa-solid fa-pencil"></i>
                        <p class="pencil-message">Edit Channel</p>
                    </button>

                    <div class="separator">
                        <p class="separator-text">
                            July 1, 2024
                        </p>
                    </div>

                    <div class="chat-message">
                        <div class="chat-message-profile-picture">
                            <img class="chat-message-image" src="/img/user-img/1.webp">
                        </div>
                        <div class="chat-message-text">
                            <p class="member-name">
                                Peterlim26
                                <span class="chat-message-time">Today at 7:32 AM</span>
                            </p>
                            <div class="chat-message-content">
                                <p>Look at this cute cat</p>
                                <a
                                    href="https://youtu.be/wE8s993ZV-8?si=Wo8DcDwnMc4ocRDU">https://youtu.be/wE8s993ZV-8?si=Wo8DcDwnMc4ocRDU</a>
                                <div class="video-preview">
                                    <p class="preview-type">
                                        YouTube
                                    </p>
                                    <p class="preview-title">
                                        Lulu the Cat
                                    </p>
                                    <p class="preview-subtitle">
                                        The kitten approaching the daddy cat to play with him was so cute.
                                    </p>
                                    <div class="preview-thumbnail-container">
                                        <img class="preview-thumbnail" src="/img/thumbnail.webp" alt="Thumbnail">
                                        <div class="thumbnail-icon-container">
                                            <i class="fa-solid fa-play thumbnail-icon"></i>
                                            <i class="fa-solid fa-arrow-up-from-bracket thumbnail-icon"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bottom-bar">
                        <div class="message-container">
                            <i class="fa-solid fa-circle-plus"></i>
                            <input class="message-bar" type="text" placeholder="Message #Gaming">
                            <i class="fa-solid fa-icons"></i>
                            <i class="fa-solid fa-gift"></i>
                            <i class="fa-solid fa-note-sticky"></i>
                            <i class="fa-solid fa-face-smile"></i>
                        </div>
                    </div>
                </div>
            </div>

            <ul class="member-list">
                <p class="role-text">
                    OWNER &mdash; 1
                </p>
                <li class="user-profile">
                    <div class="profile-picture-container">
                        <img class="profile-picture" src="/img/user-img/1.webp" alt="Profile Picture">
                        <div class="status-holder">
                            <i class="fa-solid fa-circle status-online"></i>
                        </div>
                    </div>
                    <div class="member-text">
                        <p class="member-name">Peterlim26</p>
                        <p class="member-status">Learning CSS <i class="fa-regular fa-chart-bar"></i></p>
                    </div>
                </li>

                <p class="role-text">
                    ONLINE &mdash; 4
                </p>

                <li class="user-profile">
                    <div class="profile-picture-container">
                        <img class="profile-picture" src="/img/user-img/2.webp" alt="Profile Picture">
                        <div class="status-holder">
                            <i class="fa-solid fa-circle status-online"></i>
                        </div>
                    </div>
                    <div class="member-text">
                        <p class="member-name">Eileeyyy</p>
                        <p class="member-status">Playing Minecraft <i class="fa-regular fa-chart-bar"></i></p>
                    </div>
                </li>

                <li class="user-profile">
                    <div class="profile-picture-container">
                        <img class="profile-picture" src="/img/user-img/3.webp" alt="Profile Picture">
                        <div class="status-holder">
                            <i class="fa-solid fa-circle status-online"></i>
                        </div>
                    </div>
                    <div class="member-text">
                        <p class="member-name">jhannng</p>
                    </div>
                </li>

                <li class="user-profile">
                    <div class="profile-picture-container">
                        <img class="profile-picture" src="/img/user-img/4.webp" alt="Profile Picture">
                        <div class="status-holder">
                            <i class="fa-solid fa-circle-minus status-dnd"></i>
                        </div>
                    </div>
                    <div class="member-text">
                        <p class="member-name">JiaYiXD</p>
                    </div>
                </li>

                <li class="user-profile">
                    <div class="profile-picture-container">
                        <img class="profile-picture" src="/img/user-img/5.webp" alt="Profile Picture">
                        <div class="status-holder">
                            <i class="fa-solid fa-moon fa-rotate-270 status-idle"></i>
                        </div>
                    </div>
                    <div class="member-text">
                        <p class="member-name">Jin</p>
                        <p class="member-status">Are you a VCS, cause I ... <i class="fa-regular fa-chart-bar"></i></p>

                    </div>
                </li>

                <p class="role-text">
                    OFFLINE &mdash; 14
                </p>

                <li class="user-profile offline">
                    <img class="profile-picture" src="/img/user-img/6.webp" alt="Profile Picture">
                    <div class="member-text">
                        <p class="member-name">CM</p>
                    </div>
                </li>

                <li class="user-profile offline">
                    <img class="profile-picture" src="/img/user-img/12.webp" alt="Profile Picture">
                    <div class="member-text">
                        <p class="member-name">RiceBlock126</p>
                    </div>
                </li>

                <li class="user-profile offline">
                    <img class="profile-picture" src="/img/user-img/15.webp" alt="Profile Picture">
                    <div class="member-text">
                        <p class="member-name">ming</p>
                    </div>
                </li>
            </ul>
        </div>
        <div class="error-message">
            <p class="error-text">Sorry, but this functionality has not been optimized for smaller screens. Please use a larger screen for full access.</p>
        </div>
</body>

</html>