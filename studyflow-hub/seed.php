<?php
require_once __DIR__ . '/vendor/autoload.php';

use StudyFlow\Core\Database;

echo "Bắt đầu tạo dữ liệu mẫu...\n";

try {
    $db = Database::getConnection();
    
    // 1. Users
    echo "Tạo 15 Users...\n";
    $passHash = password_hash('Password123', PASSWORD_BCRYPT);
    for ($i = 1; $i <= 15; $i++) {
        $username = "mock_user_$i";
        $email = "mock$i@test.com";
        $stmt = $db->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?) ON CONFLICT DO NOTHING RETURNING id");
        $stmt->execute([$username, $email, $passHash]);
    }

    // Lấy user_ids
    $userIds = $db->query("SELECT id FROM users LIMIT 15")->fetchAll(\PDO::FETCH_COLUMN);

    // 2. StudyFlows
    echo "Tạo 15 StudyFlows...\n";
    for ($i = 1; $i <= 15; $i++) {
        $uid = $userIds[array_rand($userIds)];
        $title = "Khóa học Mock $i";
        $slug = "khoa-hoc-mock-$i-" . time();
        $desc = "Đây là mô tả cho khóa học test số $i.";
        $stmt = $db->prepare("INSERT INTO studyflows (user_id, slug, title, description) VALUES (?, ?, ?, ?) ON CONFLICT DO NOTHING");
        $stmt->execute([$uid, $slug, $title, $desc]);
    }

    // Lấy flow_ids
    $flowIds = $db->query("SELECT id FROM studyflows LIMIT 15")->fetchAll(\PDO::FETCH_COLUMN);

    // 3. Tags
    echo "Tạo 15 Tags...\n";
    for ($i = 1; $i <= 15; $i++) {
        $name = "Chủ đề Mock $i";
        $prefix = "mock-tag-$i-" . time();
        $stmt = $db->prepare("INSERT INTO tags (name, prefix) VALUES (?, ?) ON CONFLICT DO NOTHING");
        $stmt->execute([$name, $prefix]);
    }
    
    $tagIds = $db->query("SELECT id FROM tags LIMIT 15")->fetchAll(\PDO::FETCH_COLUMN);

    // 4. Assets & Metadata
    echo "Tạo Assets, Note Metadata và Resource Metadata...\n";
    // Tạo 15 notes và 15 resources
    for ($i = 1; $i <= 15; $i++) {
        $flowId = $flowIds[array_rand($flowIds)];
        
        // Note
        $stmt = $db->prepare("INSERT INTO assets (studyflow_id, type, title, content) VALUES (?, 'note', ?, ?) RETURNING id");
        $stmt->execute([$flowId, "Note Mock $i", "Nội dung text cơ bản $i"]);
        $noteId = $stmt->fetchColumn();
        
        $db->prepare("INSERT INTO note_metadata (asset_id, markdown) VALUES (?, ?) ON CONFLICT DO NOTHING")
           ->execute([$noteId, "# Markdown Mock $i \n Đây là nội dung chi tiết"]);

        // Resource
        $stmt = $db->prepare("INSERT INTO assets (studyflow_id, type, title) VALUES (?, 'resource', ?) RETURNING id");
        $stmt->execute([$flowId, "Tài liệu Mock $i"]);
        $resourceId = $stmt->fetchColumn();

        $db->prepare("INSERT INTO resource_metadata (asset_id, filename, description) VALUES (?, ?, ?) ON CONFLICT DO NOTHING")
           ->execute([$resourceId, "document_$i.pdf", "Mô tả tài liệu $i"]);
    }

    // Lấy asset_ids
    $assetIds = $db->query("SELECT id FROM assets LIMIT 30")->fetchAll(\PDO::FETCH_COLUMN);

    // 5. Asset Tags
    echo "Gắn Tags cho Assets...\n";
    foreach ($assetIds as $aid) {
        // Gắn ngẫu nhiên 1-2 tag
        $tid = $tagIds[array_rand($tagIds)];
        $db->prepare("INSERT INTO asset_tags (asset_id, tag_id) VALUES (?, ?) ON CONFLICT DO NOTHING")->execute([$aid, $tid]);
    }

    // 6. Pins
    echo "Tạo Pins...\n";
    for ($i = 1; $i <= 15; $i++) {
        $uid = $userIds[array_rand($userIds)];
        $fid = $flowIds[array_rand($flowIds)];
        $db->prepare("INSERT INTO pins (user_id, studyflow_id) VALUES (?, ?) ON CONFLICT DO NOTHING")->execute([$uid, $fid]);
    }

    // 7. Asset Fragments
    echo "Tạo Asset Fragments...\n";
    for ($i = 1; $i <= 15; $i++) {
        $aid = $assetIds[array_rand($assetIds)];
        $tid = $tagIds[array_rand($tagIds)];
        $db->prepare("INSERT INTO asset_fragments (asset_id, tag_id, text, page) VALUES (?, ?, ?, ?) ON CONFLICT DO NOTHING")
           ->execute([$aid, $tid, "Trích đoạn quan trọng từ tài liệu $i", rand(1, 10)]);
    }

    echo "Hoàn thành! Đã tạo ít nhất 15 bản ghi cho mỗi bảng.\n";

} catch (Exception $e) {
    echo "Lỗi: " . $e->getMessage() . "\n";
}
