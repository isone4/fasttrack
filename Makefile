install:
	docker volume create --name=patric-postgres-data
	docker-compose build
	docker-compose up -d
	docker-compose exec web composer install
	docker-compose exec web php bin/console doctrine:database:create
	docker-compose exec web php bin/console doctrine:migrations:migrate
	php bin/console doctrine:migrations:migrate
run:
	docker-compose up -d
	docker-compose exec web bash