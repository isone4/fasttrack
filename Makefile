install:
	docker volume create --name=patric-postgres-data
	docker-compose build
	docker-compose up -d
	docker-compose exec web composer install --ignore-platform-reqs
	docker-compose exec web php bin/console doctrine:database:create --if-not-exists
	docker-compose exec web php bin/console doctrine:migrations:migrate -n
run:
	docker-compose up -d
	docker-compose exec web bash