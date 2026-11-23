
PRAGMA foreign_keys = ON;

-- USUÁRIOS

CREATE TABLE IF NOT EXISTS usuarios (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT NOT NULL,
    email TEXT NOT NULL
);

-- PERGUNTAS DE MÚLTIPLA ESCOLHA

CREATE TABLE IF NOT EXISTS perguntas_me (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    enunciado TEXT NOT NULL
);

-- RESPOSTAS DE MÚLTIPLA ESCOLHA

CREATE TABLE IF NOT EXISTS respostas_me (
    id INTEGER PRIMARY KEY AUTOINCREMENT,

    pergunta_id INTEGER NOT NULL
        REFERENCES perguntas_me(id)
        ON DELETE CASCADE,

    posicao INTEGER NOT NULL
        CHECK (posicao BETWEEN 0 AND 3),

    texto TEXT NOT NULL,

    correta INTEGER NOT NULL
        CHECK (correta IN (0,1)),

    UNIQUE(pergunta_id, posicao)
);

-- PERGUNTAS DISSERTATIVAS

CREATE TABLE IF NOT EXISTS perguntas_texto (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    enunciado TEXT NOT NULL
);

-- RESPOSTAS DISSERTATIVAS

CREATE TABLE IF NOT EXISTS respostas_texto (
    id INTEGER PRIMARY KEY AUTOINCREMENT,

    pergunta_id INTEGER NOT NULL UNIQUE
        REFERENCES perguntas_texto(id)
        ON DELETE CASCADE,

    gabarito TEXT NOT NULL
);
