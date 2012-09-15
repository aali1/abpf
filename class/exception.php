<?php
	
	class ABPFException extends Exception {}
	class DatabaseException extends ABPFException {}
	class DatabaseConnectionException extends DatabaseException {}
	class FailedQueryException extends DatabaseException {}
	class DuplicateKeyException extends DatabaseException {}
	class NoSuchModelException extends ABPFException {}
	class NoSuchMemberException extends ABPFException {}
	class NoSuchClassException extends ABPFException {}
	