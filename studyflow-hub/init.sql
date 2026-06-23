-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- StudyFlows Table
CREATE TABLE IF NOT EXISTS studyflows (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id) ON DELETE SET NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    is_pinned BOOLEAN DEFAULT FALSE,
    is_public BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Assets Table
CREATE TABLE IF NOT EXISTS assets (
    id SERIAL PRIMARY KEY,
    studyflow_id INT NOT NULL REFERENCES studyflows(id) ON DELETE CASCADE,
    type VARCHAR(20) NOT NULL, -- 'resource', 'note', 'folder'
    title VARCHAR(255) NOT NULL,
    content TEXT, -- Markdown note body or general text content
    storage_key VARCHAR(512), -- MinIO object path for file uploads
    mime_type VARCHAR(100),
    sort_order INT DEFAULT 0,
    tags JSONB, -- JSON array of tags for faster indexing
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Resource Metadata Table (Lab05 Module B Requirements)
CREATE TABLE IF NOT EXISTS resource_metadata (
    asset_id INT PRIMARY KEY REFERENCES assets(id) ON DELETE CASCADE,
    filename VARCHAR(255) NOT NULL,
    folder_name VARCHAR(100) DEFAULT 'Root', -- 'Slides', 'Images', 'Assignments'
    description TEXT
);

-- Note Metadata Table
CREATE TABLE IF NOT EXISTS note_metadata (
    asset_id INT PRIMARY KEY REFERENCES assets(id) ON DELETE CASCADE,
    markdown TEXT NOT NULL
);

-- Tags Table (Hierarchical supports machine-learning/cnn/yolo)
CREATE TABLE IF NOT EXISTS tags (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    prefix VARCHAR(255) NOT NULL UNIQUE
);

-- Asset Tags Mapping Table (Many-to-Many)
CREATE TABLE IF NOT EXISTS asset_tags (
    asset_id INT REFERENCES assets(id) ON DELETE CASCADE,
    tag_id INT REFERENCES tags(id) ON DELETE CASCADE,
    PRIMARY KEY (asset_id, tag_id)
);

-- Asset Fragments (for granular PDF page or image crop references)
CREATE TABLE IF NOT EXISTS asset_fragments (
    id SERIAL PRIMARY KEY,
    asset_id INT NOT NULL REFERENCES assets(id) ON DELETE CASCADE,
    tag_id INT REFERENCES tags(id) ON DELETE SET NULL,
    page INT,
    bbox TEXT,
    text TEXT,
    image_path VARCHAR(512),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Insert a default tag 'untagged'
INSERT INTO tags (name, prefix) VALUES ('untagged', 'untagged') ON CONFLICT DO NOTHING;

-- Pins Table (for pinning/favoriting StudyFlows)
CREATE TABLE IF NOT EXISTS pins (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    studyflow_id INT NOT NULL REFERENCES studyflows(id) ON DELETE CASCADE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (user_id, studyflow_id)
);
