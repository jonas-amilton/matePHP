<?php

declare(strict_types=1);

namespace Tests\Feature;

use Framework\Core\Database;
use Framework\Core\Model;
use PDO;
use PDOException;
use Tests\TestCase;

class TestUser extends Model
{
    protected static string $table = 'users';
    protected array $fillable = [
        'name',
        'email',
        'role',
        'age',
        'score',
        'active',
        'meta',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public ?int $id = null;
    public ?int $age = null;
    public ?float $score = null;
    public ?int $active = null;
    public ?string $name = null;
    public ?string $email = null;
    public ?string $role = null;
    public ?string $meta = null;
    public ?string $created_at = null;
    public ?string $updated_at = null;
    public ?string $deleted_at = null;

    public function profile(): ?array
    {
        return $this->hasOne(TestProfile::class, 'user_id', 'id');
    }

    public function posts(): array
    {
        return $this->hasMany(TestPost::class, 'user_id', 'id');
    }
}

class SoftUser extends TestUser
{
    protected static bool $softDelete = true;
}

class TestProfile extends Model
{
    protected static string $table = 'profiles';
    protected array $fillable = ['user_id', 'bio', 'created_at', 'updated_at'];

    public ?int $id = null;
    public ?int $user_id = null;
    public ?string $bio = null;
    public ?string $created_at = null;
    public ?string $updated_at = null;
}

class TestPost extends Model
{
    protected static string $table = 'posts';
    protected array $fillable = ['user_id', 'title', 'commentable_type', 'commentable_id', 'created_at', 'updated_at'];

    public ?int $id = null;
    public ?int $user_id = null;
    public ?string $title = null;
    public ?string $commentable_type = null;
    public ?int $commentable_id = null;
    public ?string $created_at = null;
    public ?string $updated_at = null;
}

class TestRole extends Model
{
    protected static string $table = 'roles';
    protected array $fillable = ['name', 'created_at', 'updated_at'];

    public ?int $id = null;
    public ?string $name = null;
    public ?string $created_at = null;
    public ?string $updated_at = null;
}

class TestComment extends Model
{
    protected static string $table = 'comments';
    protected array $fillable = ['commentable_type', 'commentable_id', 'body', 'created_at', 'updated_at'];

    public ?int $id = null;
    public ?string $commentable_type = null;
    public ?int $commentable_id = null;
    public ?string $body = null;
    public ?string $created_at = null;
    public ?string $updated_at = null;
}

class CastedUser extends Model
{
    protected static string $table = 'users';
    protected array $fillable = [
        'name',
        'email',
        'role',
        'age',
        'score',
        'active',
        'meta',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected array $casts = [
        'age' => 'int',
        'score' => 'float',
        'active' => 'bool',
        'meta' => 'json',
        'created_at' => 'datetime',
    ];

    protected ?int $age = null;
    protected ?float $score = null;
    protected ?int $active = null;
    protected ?string $name = null;
    protected ?string $meta = null;
    protected ?string $created_at = null;

    protected function getNameAttribute($value): string
    {
        return strtoupper((string) $value);
    }

    protected function setNameAttribute($value): string
    {
        return strtolower(trim((string) $value));
    }

    public function rawName(): ?string
    {
        return $this->name;
    }

    public function rawActive()
    {
        return $this->active;
    }

    public function rawMeta()
    {
        return $this->meta;
    }

    public function rawCreatedAt()
    {
        return $this->created_at;
    }
}

class ScopedUser extends TestUser
{
    protected function adminsOnly(): self
    {
        return $this->where('role', '=', 'admin');
    }
}

class ExposedUser extends TestUser
{
    public function exposedBuildCountQuery(): string
    {
        return $this->buildCountQuery();
    }

    public function exposedCountForCurrentQuery(): int
    {
        return $this->countForCurrentQuery();
    }

    public function exposedResetQuery(): void
    {
        $this->resetQuery();
    }
}

class TestUserObserver
{
    public static array $events = [];

    public static function reset(): void
    {
        self::$events = [];
    }

    public function creating(array &$payload): void
    {
        if (isset($payload['name'])) {
            $payload['name'] = strtoupper((string) $payload['name']);
        }

        self::$events[] = 'creating';
    }

    public function created(array &$payload): void
    {
        self::$events[] = 'created';
    }
}

final class ModelTest extends TestCase
{
    private PDO $pdo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        $this->injectPdo($this->pdo);
        $this->resetModelState();
        TestUserObserver::reset();
        $this->createSchema();
        $this->seedData();
    }

    protected function tearDown(): void
    {
        if (isset($this->pdo) && $this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }

        parent::tearDown();
    }

    public function test_relationship_methods_work_for_common_cases(): void
    {
        $user = new TestUser();
        $user->id = 1;

        $profile = $user->hasOne(TestProfile::class, 'user_id');
        $posts = $user->hasMany(TestPost::class, 'user_id');
        $roles = $user->belongsToMany(TestRole::class, 'user_roles', 'user_id', 'role_id');

        $this->assertSame('Bio Alice', $profile['bio']);
        $this->assertCount(2, $posts);
        $this->assertCount(2, $roles);
        $this->assertSame('admin', $roles[0]['name']);

        $profileModel = new TestProfile();
        $profileModel->user_id = 1;
        $owner = $profileModel->belongsTo(TestUser::class, 'user_id');
        $this->assertSame('Alice', $owner['name']);
    }

    public function test_with_and_lazy_loading_via_magic_getter(): void
    {
        $eager = new TestUser();
        $eager->id = 1;
        $eager->with(['profile', 'posts', 'nonexistent']);

        $this->assertSame('Bio Alice', $eager->profile['bio']);
        $this->assertCount(2, $eager->posts);

        $lazy = new TestUser();
        $lazy->id = 1;

        $this->assertSame('Bio Alice', $lazy->profile['bio']);
        $this->assertNull($lazy->unknown_property);
    }

    public function test_casts_accessors_and_mutators_are_applied(): void
    {
        $user = new CastedUser();
        $user->name = '  JoNas  ';
        $user->active = true;
        $user->meta = ['lang' => 'pt'];
        $user->created_at = new \DateTime('2026-01-01 12:00:00');
        $user->age = '42';
        $user->score = '91.25';

        $this->assertSame('jonas', $user->rawName());
        $this->assertSame('JONAS', $user->name);
        $this->assertSame(1, $user->rawActive());
        $this->assertTrue($user->active);
        $this->assertSame('{"lang":"pt"}', $user->rawMeta());
        $this->assertIsString($user->rawCreatedAt());
        $this->assertInstanceOf(\DateTime::class, $user->created_at);
        $this->assertSame(42, $user->age);
        $this->assertSame(91.25, $user->score);
    }

    public function test_macro_and_magic_call_behaviour(): void
    {
        TestUser::macro('activeOnly', function (): TestUser {
            return $this->where('active', '=', 1);
        });

        $active = (new TestUser())->activeOnly()->get();
        $this->assertCount(2, $active);

        $admin = (new ScopedUser())->adminsOnly()->first();
        $this->assertSame('Alice', $admin['name']);

        $this->expectException(\BadMethodCallException::class);
        (new TestUser())->methodThatDoesNotExist();
    }

    public function test_morph_to_and_morph_many_work(): void
    {
        $comment = new TestComment();
        $comment->commentable_type = TestPost::class;
        $comment->commentable_id = 1;

        $post = $comment->morphTo('commentable');
        $this->assertSame('Post A', $post['title']);

        $comment->commentable_type = 'App\\Unknown\\Model';
        $this->assertNull($comment->morphTo('commentable'));

        $postModel = new TestPost();
        $postModel->id = 1;
        $comments = $postModel->morphMany(TestComment::class, 'commentable');
        $this->assertCount(2, $comments);
    }

    public function test_global_scope_is_applied_automatically(): void
    {
        TestUser::addGlobalScope('only_active', function (TestUser $query): void {
            $query->where('active', '=', 1);
        });

        $users = (new TestUser())->orderBy('id', 'ASC')->get();
        $this->assertCount(2, $users);
        $this->assertSame('Alice', $users[0]['name']);
        $this->assertSame('Carol', $users[1]['name']);
    }

    public function test_query_builder_filters_and_or_where(): void
    {
        $between = (new TestUser())->whereBetween('age', 26, 40)->orderBy('id', 'ASC')->get();
        $this->assertCount(2, $between);

        $in = (new TestUser())->whereIn('id', [1, 2])->whereNotIn('id', [2])->first();
        $this->assertSame('Alice', $in['name']);

        $or = (new TestUser())
            ->where('role', '=', 'user')
            ->orWhere('role', '=', 'admin')
            ->orderBy('id', 'ASC')
            ->get();
        $this->assertCount(2, $or);
        $this->assertSame('Alice', $or[0]['name']);
        $this->assertSame('Bob', $or[1]['name']);
    }

    public function test_like_starts_with_and_ends_with_queries(): void
    {
        $this->assertSame('Alice', (new TestUser())->like('name', 'li')->first()['name']);
        $this->assertSame('Alice', (new TestUser())->startsWith('name', 'Al')->first()['name']);
        $this->assertSame('Alice', (new TestUser())->endsWith('name', 'ice')->first()['name']);
    }

    public function test_join_order_limit_offset_and_to_sql(): void
    {
        $sql = (new TestUser())
            ->join('profiles', 'id', '=', 'user_id')
            ->orderBy('id', 'DESC')
            ->limit(2)
            ->offset(1)
            ->toSql();

        $this->assertStringContainsString('INNER JOIN `profiles` ON `id` = `user_id`', $sql);
        $this->assertStringContainsString('ORDER BY `id` DESC', $sql);
        $this->assertStringContainsString('LIMIT 2', $sql);
        $this->assertStringContainsString('OFFSET 1', $sql);
    }

    public function test_get_first_all_find_and_reset_query(): void
    {
        $model = new ExposedUser();
        $rows = $model->where('active', '=', 1)->get();
        $this->assertCount(2, $rows);
        $this->assertSame('SELECT * FROM `users`', $model->toSql());

        $first = (new TestUser())->where('id', '=', 2)->first();
        $this->assertSame('Bob', $first['name']);

        $all = TestUser::all();
        $this->assertCount(3, $all);

        $found = TestUser::find(3);
        $this->assertSame('Carol', $found['name']);
    }

    public function test_create_update_and_first_or_create(): void
    {
        $new = TestUser::create([
            'name' => 'Dave',
            'email' => 'dave@example.com',
            'role' => 'user',
            'age' => 28,
            'score' => 77.0,
            'active' => 1,
        ]);

        $this->assertIsInt($new['id']);
        $this->assertArrayHasKey('created_at', $new);
        $this->assertArrayHasKey('updated_at', $new);

        $updated = TestUser::update($new['id'], ['name' => 'Dave Updated', 'active' => 0]);
        $this->assertTrue($updated);
        $stored = TestUser::find($new['id']);
        $this->assertSame('Dave Updated', $stored['name']);
        $this->assertSame(0, (int) $stored['active']);

        $existing = TestUser::firstOrCreate(['email' => 'alice@example.com'], ['name' => 'ignored']);
        $this->assertSame('Alice', $existing['name']);

        $created = TestUser::firstOrCreate(['email' => 'newfirst@example.com'], ['name' => 'First']);
        $this->assertSame('newfirst@example.com', $created['email']);
    }

    public function test_create_or_update_paths_and_validation(): void
    {
        $updated = TestUser::createOrUpdate(
            [
                'email' => 'bob@example.com',
                'name' => 'Bobby',
                'role' => 'user',
                'age' => 26,
                'score' => 82.3,
                'active' => 1,
            ],
            'email'
        );
        $this->assertSame(2, $updated['id']);
        $this->assertSame('Bobby', TestUser::find(2)['name']);

        $created = TestUser::createOrUpdate(
            [
                'email' => 'new-create-or-update@example.com',
                'name' => 'New COU',
                'role' => 'user',
                'age' => 22,
                'score' => 55.5,
                'active' => 1,
            ],
            'email'
        );
        $this->assertIsInt($created['id']);
        $this->assertSame('New COU', TestUser::find($created['id'])['name']);

        $this->expectException(\InvalidArgumentException::class);
        TestUser::createOrUpdate(['name' => 'invalid'], 'email');
    }

    public function test_update_or_create_for_existing_and_new_records(): void
    {
        $updated = TestUser::updateOrCreate(
            ['email' => 'alice@example.com'],
            ['name' => 'Alice Updated']
        );
        $this->assertSame(1, $updated['id']);
        $this->assertSame('Alice Updated', TestUser::find(1)['name']);

        $created = TestUser::updateOrCreate(
            ['email' => 'created-by-uoc@example.com'],
            ['name' => 'Created By UOC', 'role' => 'user', 'age' => 20, 'score' => 60.0, 'active' => 1]
        );
        $this->assertArrayHasKey('id', $created);
        $this->assertSame('Created By UOC', TestUser::find($created['id'])['name']);
    }

    public function test_delete_and_force_delete_in_non_soft_delete_mode(): void
    {
        $this->assertTrue(TestUser::delete(2));
        $this->assertNull(TestUser::find(2));

        $new = TestUser::create([
            'name' => 'To Remove',
            'email' => 'to-remove@example.com',
            'role' => 'user',
            'age' => 27,
            'score' => 66.0,
            'active' => 1,
        ]);

        $this->assertTrue(TestUser::forceDelete($new['id']));
        $this->assertNull(TestUser::find($new['id']));
    }

    public function test_soft_delete_restore_with_trashed_and_only_trashed(): void
    {
        $default = (new SoftUser())->orderBy('id', 'ASC')->get();
        $this->assertCount(2, $default);

        $withTrashed = (new SoftUser())->withTrashed()->orderBy('id', 'ASC')->get();
        $this->assertCount(3, $withTrashed);

        $onlyTrashed = (new SoftUser())->onlyTrashed()->orderBy('id', 'ASC')->get();
        $this->assertCount(1, $onlyTrashed);
        $this->assertSame('Carol', $onlyTrashed[0]['name']);

        $this->assertTrue(SoftUser::delete(1));
        $this->assertNull(SoftUser::find(1));

        $afterDelete = (new SoftUser())->onlyTrashed()->get();
        $this->assertCount(2, $afterDelete);

        $this->assertTrue(SoftUser::restore(1));
        $restored = SoftUser::find(1);
        $this->assertSame('Alice', $restored['name']);
    }

    public function test_aggregate_functions_return_expected_values(): void
    {
        $model = new TestUser();

        $this->assertSame(3, $model->count());
        $this->assertSame(90.0, $model->sum('age'));
        $this->assertSame(30.0, $model->avg('age'));
        $this->assertSame(25.0, $model->min('age'));
        $this->assertSame(35.0, $model->max('age'));
    }

    public function test_paginate_respects_current_query_filters(): void
    {
        $page2 = (new TestUser())
            ->where('active', '=', 1)
            ->orderBy('id', 'ASC')
            ->paginate(1, 2);

        $this->assertSame(2, $page2['total']);
        $this->assertSame(1, $page2['per_page']);
        $this->assertSame(2, $page2['current_page']);
        $this->assertSame(2, $page2['last_page']);
        $this->assertCount(1, $page2['data']);
        $this->assertSame('Carol', $page2['data'][0]['name']);
    }

    public function test_count_for_current_query_and_build_count_query(): void
    {
        $model = new ExposedUser();
        $model->where('active', '=', 1);

        $sql = $model->exposedBuildCountQuery();
        $this->assertSame('SELECT COUNT(*) AS total FROM `users` WHERE `active` = ?', $sql);
        $this->assertSame(2, $model->exposedCountForCurrentQuery());
    }

    public function test_transactions_begin_commit_and_rollback(): void
    {
        TestUser::beginTransaction();
        TestUser::create([
            'name' => 'Tx Rollback',
            'email' => 'tx-rollback@example.com',
            'role' => 'user',
            'age' => 22,
            'score' => 50,
            'active' => 1,
        ]);
        TestUser::rollback();
        $this->assertNull((new TestUser())->where('email', '=', 'tx-rollback@example.com')->first());

        TestUser::beginTransaction();
        TestUser::create([
            'name' => 'Tx Commit',
            'email' => 'tx-commit@example.com',
            'role' => 'user',
            'age' => 23,
            'score' => 51,
            'active' => 1,
        ]);
        TestUser::commit();
        $this->assertNotNull((new TestUser())->where('email', '=', 'tx-commit@example.com')->first());
    }

    public function test_event_callbacks_are_fired(): void
    {
        $events = [];

        TestUser::on('creating', function (array $payload) use (&$events): void {
            $events['creating'] = $payload;
        });

        TestUser::on('created', function (array $payload) use (&$events): void {
            $events['created'] = $payload;
        });

        TestUser::create([
            'name' => 'Event User',
            'email' => 'event-user@example.com',
            'role' => 'user',
            'age' => 21,
            'score' => 60,
            'active' => 1,
        ]);

        $this->assertArrayHasKey('creating', $events);
        $this->assertArrayHasKey('created', $events);
        $this->assertSame('event-user@example.com', $events['created']['email']);
    }

    public function test_observer_mutates_payload_and_receives_events(): void
    {
        TestUser::observe(TestUserObserver::class);

        $created = TestUser::create([
            'name' => 'observer user',
            'email' => 'observer@example.com',
            'role' => 'user',
            'age' => 31,
            'score' => 72,
            'active' => 1,
        ]);

        $stored = TestUser::find($created['id']);
        $this->assertSame('OBSERVER USER', $stored['name']);
        $this->assertSame(['creating', 'created'], TestUserObserver::$events);
    }

    public function test_lock_for_update_throws_on_sqlite(): void
    {
        $this->expectException(PDOException::class);
        (new TestUser())->lockForUpdate('id', '=', 1);
    }

    private function injectPdo(PDO $pdo): void
    {
        $reflection = new \ReflectionClass(Database::class);
        $property = $reflection->getProperty('pdo');
        $property->setValue(null, $pdo);
    }

    private function resetModelState(): void
    {
        $reflection = new \ReflectionClass(Model::class);

        $events = [
            'creating' => [],
            'created' => [],
            'updating' => [],
            'updated' => [],
            'deleting' => [],
            'deleted' => [],
            'restoring' => [],
            'restored' => [],
        ];

        $reflection->getProperty('macros')->setValue(null, []);
        $reflection->getProperty('globalScopes')->setValue(null, []);
        $reflection->getProperty('observers')->setValue(null, []);
        $reflection->getProperty('events')->setValue(null, $events);
    }

    private function createSchema(): void
    {
        $this->pdo->exec(
            'CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT,
                email TEXT UNIQUE,
                role TEXT,
                age INTEGER,
                score REAL,
                active INTEGER,
                meta TEXT,
                created_at TEXT,
                updated_at TEXT,
                deleted_at TEXT
            )'
        );

        $this->pdo->exec(
            'CREATE TABLE profiles (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER,
                bio TEXT,
                created_at TEXT,
                updated_at TEXT
            )'
        );

        $this->pdo->exec(
            'CREATE TABLE posts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER,
                title TEXT,
                commentable_type TEXT,
                commentable_id INTEGER,
                created_at TEXT,
                updated_at TEXT
            )'
        );

        $this->pdo->exec(
            'CREATE TABLE roles (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT,
                created_at TEXT,
                updated_at TEXT
            )'
        );

        $this->pdo->exec(
            'CREATE TABLE user_roles (
                user_id INTEGER,
                role_id INTEGER
            )'
        );

        $this->pdo->exec(
            'CREATE TABLE comments (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                commentable_type TEXT,
                commentable_id INTEGER,
                body TEXT,
                created_at TEXT,
                updated_at TEXT
            )'
        );
    }

    private function seedData(): void
    {
        $now = '2026-02-28 10:00:00';
        $deletedAt = '2026-02-20 09:00:00';

        $this->pdo->exec(
            "INSERT INTO users (id, name, email, role, age, score, active, meta, created_at, updated_at, deleted_at) VALUES
            (1, 'Alice', 'alice@example.com', 'admin', 30, 95.5, 1, '{\"lang\":\"pt\"}', '{$now}', '{$now}', NULL),
            (2, 'Bob', 'bob@example.com', 'user', 25, 80.0, 0, '{\"lang\":\"en\"}', '{$now}', '{$now}', NULL),
            (3, 'Carol', 'carol@example.com', 'manager', 35, 70.0, 1, '{\"lang\":\"es\"}', '{$now}', '{$now}', '{$deletedAt}')"
        );

        $this->pdo->exec(
            "INSERT INTO profiles (id, user_id, bio, created_at, updated_at) VALUES
            (1, 1, 'Bio Alice', '{$now}', '{$now}'),
            (2, 2, 'Bio Bob', '{$now}', '{$now}')"
        );

        $this->pdo->exec(
            "INSERT INTO posts (id, user_id, title, commentable_type, commentable_id, created_at, updated_at) VALUES
            (1, 1, 'Post A', NULL, NULL, '{$now}', '{$now}'),
            (2, 1, 'Post B', NULL, NULL, '{$now}', '{$now}'),
            (3, 2, 'Post C', NULL, NULL, '{$now}', '{$now}')"
        );

        $this->pdo->exec(
            "INSERT INTO roles (id, name, created_at, updated_at) VALUES
            (1, 'admin', '{$now}', '{$now}'),
            (2, 'editor', '{$now}', '{$now}')"
        );

        $this->pdo->exec(
            'INSERT INTO user_roles (user_id, role_id) VALUES
            (1, 1),
            (1, 2),
            (2, 2)'
        );

        $postClass = TestPost::class;
        $this->pdo->exec(
            "INSERT INTO comments (id, commentable_type, commentable_id, body, created_at, updated_at) VALUES
            (1, '{$postClass}', 1, 'Comment 1', '{$now}', '{$now}'),
            (2, '{$postClass}', 1, 'Comment 2', '{$now}', '{$now}'),
            (3, '{$postClass}', 3, 'Comment 3', '{$now}', '{$now}')"
        );
    }
}
