install:
	docker volume create --name=patric-postgres-data
	docker-compose up -d
	docker-compose exec web bash
	composer install
	php bin/console doctrine:migrations:migrate
run:
	docker-compose up -d
	docker-compose exec web bash