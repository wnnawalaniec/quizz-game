start:
	docker-compose up -d

stop:
	docker-compose stop

logs:
	docker-compose logs -f

setup:
	cp .env.dist .env
	composer install

schema-update:
	docker-compose up -d
	docker exec quiz-game_fpm php vendor/bin/doctrine orm:schema-tool:update --force
