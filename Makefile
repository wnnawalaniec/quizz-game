start:
	docker-compose up -d

stop:
	docker-compose stop

logs:
	docker-compose logs -f

setup:
	cp .env.dist .env
	composer install
