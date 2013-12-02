<?php

include_once './conexao/conexao.php';
conectar("CORPORERM");


//$data = date('Y-m-d');

$query = "INSERT INTO auxorquestra..evasaoFaltasLista
            SELECT ULISTAPRESENCA.PERLETIVO,ULISTAPRESENCA.CODTUR,ULISTAPRESENCA.CODMAT,ULISTAPRESENCA.MATALUNO,ULISTAPRESENCA.CODCOLIGADA,DATAAULA,ULISTAPRESENCA.STATUS,
            Count(UMATALUN.codmat) DISCIPLINAS,ROW_NUMBER () OVER (PARTITION BY ULISTAPRESENCA.CODCOLIGADA,ULISTAPRESENCA.MATALUNO,ULISTAPRESENCA.CODTUR,ULISTAPRESENCA.CODMAT
            ORDER BY ULISTAPRESENCA.CODCOLIGADA,ULISTAPRESENCA.MATALUNO,ULISTAPRESENCA.CODTUR,ULISTAPRESENCA.CODMAT,DATAAULA ) IDAULA,
            UMATALUN.CARGAHORARIA
            FROM ULISTAPRESENCA
            INNER JOIN UMATALUN ON
            ULISTAPRESENCA.PERLETIVO= UMATALUN.PERLETIVO AND
            UMATALUN.MATALUNO = ULISTAPRESENCA.MATALUNO AND
            UMATALUN.CODCOLIGADA = ULISTAPRESENCA.CODCOLIGADA AND
            UMATALUN.CODMAT = ULISTAPRESENCA.CODMAT AND
            UMATALUN.CODTUR = ULISTAPRESENCA.CODTUR
            WHERE DATAAULA >= '2013-08-01 00:00:00.000' AND CODTIPOMATRICULA = 1 AND UMATALUN.STATUS = 02 AND 
            cast(ULISTAPRESENCA.CODCOLIGADA as varchar)+cast(ULISTAPRESENCA.MATALUNO as varchar) COLLATE Latin1_General_CI_AS NOT IN     
            (select codcoligada+matricula FALTA from AUXORQUESTRA..evasaoFaltas )
            GROUP BY ULISTAPRESENCA.CODCOLIGADA,ULISTAPRESENCA.PERLETIVO,ULISTAPRESENCA.CODTUR,ULISTAPRESENCA.CODMAT,ULISTAPRESENCA.MATALUNO,NUMAULA,DATAAULA,ULISTAPRESENCA.STATUS,
            UMATALUN.CARGAHORARIA
";

mssql_query($query);

$query = "delete from auxorquestra..evasaoFaltasLista where perletivo  <>'2013/2'";

mssql_query($query);


echo 'fim';